<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Subject;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ImportQuestions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = QuestionResource::class;

    protected static ?string $title = 'Import Questions';

    protected string $view = 'filament.resources.questions.pages.import-questions';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'format' => 'csv',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn (): array => Subject::query()->orderBy('code')->pluck('code', 'id')->all())
                    ->searchable()
                    ->required(),
                Radio::make('format')
                    ->label('File Type')
                    ->options([
                        'csv' => 'CSV',
                        'json' => 'JSON',
                    ])
                    ->inline()
                    ->default('csv')
                    ->required(),
                FileUpload::make('file')
                    ->label('Upload File')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/json',
                    ])
                    ->maxSize(10240)
                    ->storeFiles(false)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        try {
            $questions = match ($state['format'] ?? 'csv') {
                'json' => $this->parseJson($state['file'] ?? null),
                default => $this->parseCsv($state['file'] ?? null),
            };
        } catch (ValidationException $exception) {
            $this->notifyFailure($exception->getMessage());
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->notifyFailure('An unexpected error occurred while reading the import file.');

            return;
        }

        try {
            $imported = $this->storeQuestions(
                (int) ($state['subject_id'] ?? 0),
                $questions,
            );
        } catch (ValidationException $exception) {
            $this->notifyFailure($exception->getMessage());
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->notifyFailure('An unexpected error occurred while importing the questions.');

            return;
        }

        Notification::make()
            ->title('Questions imported')
            ->success()
            ->body("{$imported} question(s) imported successfully.")
            ->send();

        $this->form->fill([
            'format' => $state['format'] ?? 'csv',
        ]);

        $this->redirect(QuestionResource::getUrl('index'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseCsv(mixed $file): array
    {
        $path = $this->resolveUploadedFilePath($file);

        if (($handle = fopen($path, 'rb')) === false) {
            throw ValidationException::withMessages([
                'file' => 'Unable to read the uploaded CSV file.',
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => 'The CSV file is empty.',
            ]);
        }

        $header = array_map(static fn (mixed $value): string => strtolower(trim((string) $value)), $header);

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, static fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = array_pad($row, count($header), null);
        }

        fclose($handle);

        $questions = [];

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            if ($data === false) {
                continue;
            }

            $questions[] = $this->normalizeCsvRow($data);
        }

        if (empty($questions)) {
            throw ValidationException::withMessages([
                'file' => 'No questions were found in the CSV file.',
            ]);
        }

        return $questions;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function normalizeCsvRow(array $data): array
    {
        $stem = trim((string) ($data['stem'] ?? ''));
        $difficulty = strtolower(trim((string) ($data['difficulty'] ?? '')));
        $explanation = trim((string) ($data['explanation'] ?? ''));
        $correct = strtolower(trim((string) ($data['correct'] ?? '')));

        $choiceColumns = [
            'choice_a',
            'choice_b',
            'choice_c',
            'choice_d',
            'choice_e',
            'choice_f',
        ];

        $choices = [];

        foreach ($choiceColumns as $index => $column) {
            $text = trim((string) ($data[$column] ?? ''));

            if ($text === '') {
                continue;
            }

            $letter = chr(97 + $index);

            $choices[] = [
                'label' => strtoupper($letter),
                'text' => $text,
                'is_correct' => $correct === $letter,
                'order' => $index,
            ];
        }

        return $this->validateChoiceSet($stem, $difficulty, $explanation, $choices);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseJson(mixed $file): array
    {
        $path = $this->resolveUploadedFilePath($file);
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw ValidationException::withMessages([
                'file' => 'Unable to read the uploaded JSON file.',
            ]);
        }

        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'file' => 'The JSON file is malformed.',
            ]);
        }

        $questions = [];

        foreach ($payload as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $stem = trim((string) ($item['stem'] ?? ''));
            $difficulty = strtolower(trim((string) ($item['difficulty'] ?? '')));
            $explanation = trim((string) ($item['explanation'] ?? ''));
            $choicesPayload = Arr::wrap($item['choices'] ?? []);

            $choices = [];

            foreach ($choicesPayload as $choiceIndex => $choice) {
                if (! is_array($choice)) {
                    continue;
                }

                $text = trim((string) ($choice['text'] ?? ''));

                if ($text === '') {
                    continue;
                }

                $choices[] = [
                    'label' => $choice['label'] ?? strtoupper(chr(65 + $choiceIndex)),
                    'text' => $text,
                    'is_correct' => (bool) ($choice['is_correct'] ?? false),
                    'order' => $choiceIndex,
                ];
            }

            $questions[] = $this->validateChoiceSet($stem, $difficulty, $explanation, $choices);
        }

        if (empty($questions)) {
            throw ValidationException::withMessages([
                'file' => 'No questions were found in the JSON file.',
            ]);
        }

        return $questions;
    }

    /**
     * @param  array<int, array<string, mixed>>  $choices
     * @return array<string, mixed>
     */
    private function validateChoiceSet(string $stem, string $difficulty, string $explanation, array $choices): array
    {
        if ($stem === '') {
            throw ValidationException::withMessages([
                'file' => 'Each question requires a stem.',
            ]);
        }

        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Difficulty must be one of: easy, medium, hard.',
            ]);
        }

        if ($explanation === '') {
            throw ValidationException::withMessages([
                'file' => 'Each question requires an explanation.',
            ]);
        }

        if (count($choices) < 2) {
            throw ValidationException::withMessages([
                'file' => 'Each question must include at least two choices.',
            ]);
        }

        $correctCount = collect($choices)->whereStrict('is_correct', true)->count();

        if ($correctCount !== 1) {
            throw ValidationException::withMessages([
                'file' => 'Each question must have exactly one correct choice.',
            ]);
        }

        return [
            'stem' => $stem,
            'difficulty' => $difficulty,
            'explanation' => $explanation,
            'choices' => array_values($choices),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function storeQuestions(int $subjectId, array $questions): int
    {
        if ($subjectId === 0) {
            throw ValidationException::withMessages([
                'subject_id' => 'Select a subject before importing.',
            ]);
        }

        return DB::transaction(function () use ($subjectId, $questions): int {
            $count = 0;

            foreach ($questions as $questionData) {
                $question = Question::create([
                    'subject_id' => $subjectId,
                    'stem' => $questionData['stem'],
                    'difficulty' => $questionData['difficulty'],
                    'explanation' => $questionData['explanation'],
                ]);

                $question->choices()->createMany(
                    collect($questionData['choices'])
                        ->values()
                        ->map(function (array $choice, int $index): array {
                            return [
                                'label' => $choice['label'] ?? strtoupper(chr(65 + $index)),
                                'text' => $choice['text'],
                                'is_correct' => (bool) ($choice['is_correct'] ?? false),
                                'order' => $choice['order'] ?? $index,
                            ];
                        })
                        ->all()
                );

                $count++;
            }

            return $count;
        });
    }

    private function resolveUploadedFilePath(mixed $file): string
    {
        if ($file instanceof TemporaryUploadedFile) {
            return $file->getRealPath();
        }

        if (is_string($file) && $file !== '') {
            return Storage::path($file);
        }

        throw ValidationException::withMessages([
            'file' => 'Upload a CSV or JSON file before importing.',
        ]);
    }

    private function notifyFailure(string $message): void
    {
        Notification::make()
            ->title('Import failed')
            ->danger()
            ->body($message)
            ->send();
    }
}
