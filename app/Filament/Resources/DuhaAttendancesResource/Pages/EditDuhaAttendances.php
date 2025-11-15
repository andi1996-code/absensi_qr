<?php

namespace App\Filament\Resources\DuhaAttendancesResource\Pages;

use App\Filament\Resources\DuhaAttendancesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon;

class EditDuhaAttendances extends EditRecord
{
    protected static string $resource = DuhaAttendancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan data sudah loaded correctly
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate is_late based on scanned_at time
        $scannedAt = Carbon::parse($data['scanned_at']);

        // Get date from scanned_at
        $scanDate = $scannedAt->copy()->startOfDay();

        // Set max time untuk absen duha (jam 8 pagi pada hari yang sama)
        $duhaMaxTime = $scanDate->copy()->setTime(8, 0, 0); // Waktu absen maksimal: 08:00

        $lateMinutes = 0;
        if ($scannedAt->greaterThan($duhaMaxTime)) {
            // Terlambat - hitung selisih dalam detik kemudian convert ke menit
            $diffInSeconds = $scannedAt->diffInSeconds($duhaMaxTime);
            $lateMinutes = (int) ceil($diffInSeconds / 60);
        }

        $data['is_late'] = $lateMinutes;

        return $data;
    }

    protected function afterSave(): void
    {
        // Refresh record data after save
        $this->record->refresh();
    }
}
