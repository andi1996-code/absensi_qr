<?php

namespace App\Filament\Resources\SalaryGenerationResource\Pages;

use App\Filament\Resources\SalaryGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaryGenerations extends ListRecords
{
    protected static string $resource = SalaryGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Generate Gaji Baru'),
        ];
    }
}
