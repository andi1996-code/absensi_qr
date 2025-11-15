<?php

namespace App\Filament\Resources\PositionSalaryResource\Pages;

use App\Filament\Resources\PositionSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPositionSalary extends EditRecord
{
    protected static string $resource = PositionSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
