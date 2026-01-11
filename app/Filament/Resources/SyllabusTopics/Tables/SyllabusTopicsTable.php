<?php

namespace App\Filament\Resources\SyllabusTopics\Tables;

use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SyllabusTopicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.code')
                    ->label('Subject')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('syllabusVersion.code')
                    ->label('Version')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('topic_code')
                    ->label('Topic Code')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Title')
                    ->formatStateUsing(function (string $state, SyllabusTopic $record): string {
                        $depth = max(0, (int) $record->depth);

                        $indentation = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);

                        return $indentation.e($state);
                    })
                    ->html()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('depth')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('children_count')
                    ->label('Children')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'gray' : 'primary')
                    ->sortable(),
                TextColumn::make('is_leaf')
                    ->label('Leaf')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Leaf' : 'Branch')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('subject')
                    ->relationship('subject', 'code')
                    ->label('Subject'),
                SelectFilter::make('syllabusVersion')
                    ->relationship('syllabusVersion', 'code')
                    ->label('Syllabus Version')
                    ->default(fn (): ?string => SyllabusVersion::query()
                        ->orderByDesc('effective_date')
                        ->value('id')),
                SelectFilter::make('is_leaf')
                    ->label('Leaf')
                    ->options([
                        '1' => 'Leaf',
                        '0' => 'Non-leaf',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->modifyQueryUsing(function (Builder $query): void {
                $query
                    ->with(['subject', 'syllabusVersion'])
                    ->withCount('children')
                    ->orderBy('subject_id')
                    ->orderBy('depth')
                    ->orderBy('display_order');
            });
    }
}
