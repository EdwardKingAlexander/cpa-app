<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Actions\Action as TablesAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use UnitEnum;
use BackedEnum;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'stem';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'code')
                    ->label('Subject')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('stem')
                    ->label('Stem')
                    ->rows(6)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('difficulty')
                    ->label('Difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ])
                    ->native(false)
                    ->default('medium')
                    ->required(),
                Textarea::make('explanation')
                    ->label('Explanation')
                    ->rows(6)
                    ->required()
                    ->helperText('Shown after the learner answers to reinforce the concept.')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('choices')
                    ->label('Choices')
                    ->relationship()
                    ->minItems(2)
                    ->maxItems(6)
                    ->defaultItems(4)
                    ->orderColumn('order')
                    ->reorderableWithDragAndDrop()
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Label')
                            ->placeholder('A')
                            ->maxLength(5)
                            ->columnSpan(1),
                        Textarea::make('text')
                            ->label('Choice Text')
                            ->rows(3)
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Toggle::make('is_correct')
                            ->label('Correct')
                            ->inline(false)
                            ->live()
                            ->afterStateUpdated(static function (Component $component, ?bool $state, Set $set): void {
                                if (! $state) {
                                    return;
                                }

                                $repeater = $component->getParentRepeater();

                                if (! $repeater) {
                                    return;
                                }

                                $repeaterStatePath = $repeater->getStatePath();

                                $componentStatePath = $component->getStatePath();

                                $currentItemKey = (string) str($componentStatePath)
                                    ->after("{$repeaterStatePath}.")
                                    ->beforeLast('.is_correct');

                                $siblings = Arr::except($repeater->getRawState() ?? [], [$currentItemKey]);

                                foreach ($siblings as $itemKey => $itemState) {
                                    if (! data_get($itemState, 'is_correct')) {
                                        continue;
                                    }

                                    $set(
                                        path: "{$repeaterStatePath}.{$itemKey}.is_correct",
                                        state: false,
                                        isAbsolute: true,
                                    );
                                }
                            })
                            ->columnSpan(1),
                    ])
                    ->addActionLabel('Add choice')
                    ->helperText('Provide 2-6 choices and mark exactly one as correct.')
                    ->rule(static function (): Closure {
                        return static function (string $attribute, mixed $value, Closure $fail): void {
                            $choices = collect($value ?? []);
                            $correctCount = $choices->where('is_correct', true)->count();

                            if ($correctCount !== 1) {
                                $fail('Each question must have exactly one correct choice.');
                            }
                        };
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.code')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stem')
                    ->label('Stem')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                BadgeColumn::make('difficulty')
                    ->label('Difficulty')
                    ->colors([
                        'success' => 'easy',
                        'warning' => 'medium',
                        'danger' => 'hard',
                    ])
                    ->formatStateUsing(fn (string $state): string => Str::ucfirst($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                TablesAction::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-m-document-duplicate')
                    ->requiresConfirmation()
                    ->action(function (Question $record): void {
                        DB::transaction(function () use ($record): void {
                            $duplicate = $record->replicate();
                            $duplicate->save();

                            $record->choices
                                ->each(function ($choice) use ($duplicate): void {
                                    $duplicate->choices()->create([
                                        'label' => $choice->label,
                                        'text' => $choice->text,
                                        'is_correct' => (bool) $choice->is_correct,
                                        'order' => $choice->order,
                                    ]);
                                });
                        });

                        Notification::make()
                            ->title('Question duplicated')
                            ->success()
                            ->body('A duplicate of the question and its choices has been created.')
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
            'import' => Pages\ImportQuestions::route('/import'),
        ];
    }
}
