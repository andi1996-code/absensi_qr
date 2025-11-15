<?php

namespace App\Filament\Resources\SalariesResource\Pages;

use App\Filament\Resources\SalariesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaries extends EditRecord
{
    protected static string $resource = SalariesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
