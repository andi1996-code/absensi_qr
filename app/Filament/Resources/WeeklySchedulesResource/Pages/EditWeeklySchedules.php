<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Filament\Resources\WeeklySchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeeklySchedules extends EditRecord
{
    protected static string $resource = WeeklySchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
