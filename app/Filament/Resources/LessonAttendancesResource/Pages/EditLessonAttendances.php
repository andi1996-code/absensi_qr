<?php

namespace App\Filament\Resources\LessonAttendancesResource\Pages;

use App\Filament\Resources\LessonAttendancesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLessonAttendances extends EditRecord
{
    protected static string $resource = LessonAttendancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
