<?php

namespace App\Filament\Resources\DepartureAttendancesResource\Pages;

use App\Filament\Resources\DepartureAttendancesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartureAttendances extends ListRecords
{
    protected static string $resource = DepartureAttendancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
