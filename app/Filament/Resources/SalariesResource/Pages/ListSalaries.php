<?php

namespace App\Filament\Resources\SalariesResource\Pages;

use App\Filament\Resources\SalariesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaries extends ListRecords
{
    protected static string $resource = SalariesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
