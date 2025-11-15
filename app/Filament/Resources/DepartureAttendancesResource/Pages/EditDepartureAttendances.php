<?php

namespace App\Filament\Resources\DepartureAttendancesResource\Pages;

use App\Filament\Resources\DepartureAttendancesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon;

class EditDepartureAttendances extends EditRecord
{
    protected static string $resource = DepartureAttendancesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate is_late (early departure) based on scanned_at time
        $scannedAt = Carbon::parse($data['scanned_at']);

        // Get date from scanned_at
        $scanDate = $scannedAt->copy()->startOfDay();

        // Set min time untuk absen pulang (jam 14:10 sore pada hari yang sama)
        $departureNormalTime = $scanDate->copy()->setTime(14, 10, 0); // Waktu pulang normal: 14:10

        $earlyMinutes = 0;
        if ($scannedAt->lessThan($departureNormalTime)) {
            // Pulang awal - hitung selisih dalam detik kemudian convert ke menit
            $diffInSeconds = $departureNormalTime->diffInSeconds($scannedAt);
            $earlyMinutes = (int) ceil($diffInSeconds / 60);
        }

        $data['is_late'] = $earlyMinutes;

        return $data;
    }
}
