<?php

namespace App\Filament\Resources\SalaryGenerationResource\Pages;

use App\Filament\Resources\SalaryGenerationResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\SalaryCalculationService;
use App\Models\Teachers;
use Filament\Notifications\Notification;

class CreateSalaryGeneration extends CreateRecord
{
    protected static string $resource = SalaryGenerationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $teacher = Teachers::find($data['teacher_id']);
        $service = new SalaryCalculationService();

        // Hitung gaji pokok menggunakan method baru
        $calculation = $service->calculateBaseSalary($teacher, $data['year'], $data['month']);

        // Hitung total gaji pokok (termasuk tunjangan jabatan)
        $totalBaseSalary = $calculation['base_salary'] + $calculation['position_allowance'];
        $grandTotal = $totalBaseSalary + ($data['additional_amount'] ?? 0);

        // Tambahkan data perhitungan ke form data
        $data['total_scheduled_hours'] = $calculation['scheduled_hours'];
        $data['attended_hours'] = $calculation['attended_hours'];
        $data['absent_hours'] = $calculation['absent_hours'];
        $data['total_amount'] = $totalBaseSalary; // Gaji pokok + tunjangan jabatan
        $data['additional_amount'] = $data['additional_amount'] ?? 0;
        $data['additional_notes'] = $data['additional_notes'] ?? null;
        $data['is_paid'] = false;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Gaji berhasil di-generate')
            ->body('Data gaji guru telah berhasil dibuat.');
    }
}
