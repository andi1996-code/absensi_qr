<?php

namespace App\Filament\Resources\TeachersResource\Pages;

use App\Filament\Resources\TeachersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeachers extends EditRecord
{
    protected static string $resource = TeachersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
