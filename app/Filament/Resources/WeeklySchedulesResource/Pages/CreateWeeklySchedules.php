<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Filament\Resources\WeeklySchedulesResource;
use App\Models\WeeklySchedules;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateWeeklySchedules extends CreateRecord
{
    protected static string $resource = WeeklySchedulesResource::class;

    public function create(bool $another = false): void
    {
        // Ambil data form dari Livewire component
        $data = $this->form->getState();

        $teacherId = $data['teacher_id'] ?? null;
        $schedules = $data['schedules'] ?? [];

        if (empty($teacherId)) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Pilih guru terlebih dahulu')
                ->send();
            return;
        }

        if (empty($schedules)) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Minimal ada satu jadwal yang harus diisi')
                ->send();
            return;
        }

        // Buat multiple schedule records
        $count = 0;
        foreach ($schedules as $schedule) {
            // Validasi: harus ada day_of_week dan schedule_time_id
            if (!empty($schedule['day_of_week']) && !empty($schedule['schedule_time_id'])) {
                // Ambil schedule time untuk mendapatkan hour_number
                $scheduleTime = \App\Models\ScheduleTime::find($schedule['schedule_time_id']);

                if ($scheduleTime) {
                    WeeklySchedules::create([
                        'teacher_id' => $teacherId,
                        'day_of_week' => $schedule['day_of_week'],
                        'schedule_time_id' => $schedule['schedule_time_id'],
                        'hour_number' => $scheduleTime->hour_number,
                    ]);
                    $count++;
                }
            }
        }

        if ($count === 0) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Tidak ada jadwal yang valid')
                ->send();
            return;
        }

        Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body("Jadwal berhasil ditambahkan ({$count} jadwal)")
            ->send();

        if (!$another) {
            $this->redirect($this->getRedirectUrl());
        } else {
            $this->form->fill();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
