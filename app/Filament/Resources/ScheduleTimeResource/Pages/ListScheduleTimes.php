<?php

namespace App\Filament\Resources\ScheduleTimeResource\Pages;

use App\Filament\Resources\ScheduleTimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleTimes extends ListRecords
{
    protected static string $resource = ScheduleTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
