<?php

namespace App\Filament\Resources\SyllabusTopics\Tables;

use App\Models\SyllabusTopic;
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
                    ->sortable(),
                TextColumn::make('topic_code')
                    ->label('Topic Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Title')
                    ->formatStateUsing(function (string $state, SyllabusTopic $record): string {
                        $depth = max(0, (int) $record->depth);

                        return str_repeat('-- ', $depth).$state;
                    })
                    ->searchable()
                    ->wrap(),
                TextColumn::make('depth')
                    ->sortable(),
                TextColumn::make('children_count')
                    ->label('Children')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject')
                    ->relationship('subject', 'code'),
                SelectFilter::make('syllabusVersion')
                    ->relationship('syllabusVersion', 'code')
                    ->label('Syllabus Version'),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->modifyQueryUsing(function (Builder $query): void {
                $query
                    ->withCount('children')
                    ->orderBy('subject_id')
                    ->orderBy('depth')
                    ->orderBy('display_order');
            });
    }
}
