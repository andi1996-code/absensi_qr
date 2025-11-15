<?php

namespace App\Filament\Resources\TeachersResource\Pages;

use App\Filament\Resources\TeachersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTeachers extends CreateRecord
{
    protected static string $resource = TeachersResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate QR code otomatis jika kosong
        if (empty($data['qr_code'])) {
            $data['qr_code'] = $this->generateUniqueQrCode();
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Guru Berhasil Ditambahkan')
            ->body('Guru baru telah ditambahkan dengan QR Code: ' . $this->record->qr_code)
            ->send();
    }

    /**
     * Generate unique QR code
     */
    private function generateUniqueQrCode(): string
    {
        $model = static::$resource::getModel();

        do {
            $qrCode = 'QR-' . strtoupper(\Illuminate\Support\Str::random(10));
        } while ($model::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}

