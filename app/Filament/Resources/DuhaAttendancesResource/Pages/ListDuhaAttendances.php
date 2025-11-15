<?php

namespace App\Filament\Resources\DuhaAttendancesResource\Pages;

use App\Filament\Resources\DuhaAttendancesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDuhaAttendances extends ListRecords
{
    protected static string $resource = DuhaAttendancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
