<?php

namespace App\Filament\Resources\DurationSettingResource\Pages;

use App\Filament\Resources\DurationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDurationSettings extends ListRecords
{
    protected static string $resource = DurationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
