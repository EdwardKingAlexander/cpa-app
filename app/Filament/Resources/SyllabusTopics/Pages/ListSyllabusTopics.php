<?php

namespace App\Filament\Resources\SyllabusTopics\Pages;

use App\Filament\Resources\SyllabusTopics\SyllabusTopicResource;
use Filament\Resources\Pages\ListRecords;

class ListSyllabusTopics extends ListRecords
{
    protected static string $resource = SyllabusTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
