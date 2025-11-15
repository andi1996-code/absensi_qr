<?php

namespace App\Filament\Resources\SalaryGenerationResource\Pages;

use App\Filament\Resources\SalaryGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSalaryGeneration extends EditRecord
{
    protected static string $resource = SalaryGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data gaji berhasil diperbarui')
            ->body('Perubahan pada data gaji telah disimpan.');
    }
}
