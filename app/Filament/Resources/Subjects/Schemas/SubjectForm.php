<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('syllabus_code')
                    ->label('Syllabus Code'),
                TextInput::make('exam_question_count')
                    ->label('Exam Question Count')
                    ->numeric()
                    ->minValue(1),
                Select::make('default_syllabus_version_id')
                    ->label('Default Syllabus Version')
                    ->relationship('defaultSyllabusVersion', 'code')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
