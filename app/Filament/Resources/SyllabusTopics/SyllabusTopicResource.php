<?php

namespace App\Filament\Resources\SyllabusTopics;

use App\Filament\Resources\SyllabusTopics\Pages\ListSyllabusTopics;
use App\Filament\Resources\SyllabusTopics\Schemas\SyllabusTopicForm;
use App\Filament\Resources\SyllabusTopics\Tables\SyllabusTopicsTable;
use App\Models\SyllabusTopic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SyllabusTopicResource extends Resource
{
    protected static ?string $model = SyllabusTopic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SyllabusTopicForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SyllabusTopicsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSyllabusTopics::route('/'),
        ];
    }
}
