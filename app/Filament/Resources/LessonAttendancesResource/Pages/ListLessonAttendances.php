<?php

namespace App\Filament\Resources\LessonAttendancesResource\Pages;

use App\Filament\Resources\LessonAttendancesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessonAttendances extends ListRecords
{
    protected static string $resource = LessonAttendancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
