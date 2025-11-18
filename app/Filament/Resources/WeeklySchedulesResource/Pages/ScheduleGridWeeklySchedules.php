<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Filament\Resources\WeeklySchedulesResource;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Models\ScheduleTime;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScheduleGridWeeklySchedules extends ListRecords
{
    protected static string $resource = WeeklySchedulesResource::class;

    protected static string $view = 'filament.resources.weekly-schedules-resource.pages.schedule-grid';

    public ?int $selectedTeacherId = null;

    public function mount(): void
    {
        parent::mount();
    }

    public function getTeachers()
    {
        return Teachers::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getScheduleGrid()
    {
        if (!$this->selectedTeacherId) {
            return [];
        }

        $teacher = Teachers::find($this->selectedTeacherId);
        if (!$teacher) {
            return [];
        }

        // Days of week
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        // Use user-defined lesson times
        $lessonTimes = ScheduleTime::where('is_lesson', true)
            ->orderBy('hour_number')
            ->get();

        $schedules = WeeklySchedules::where('teacher_id', $teacher->id)->get();

        $grid = [];
        foreach ($lessonTimes as $lt) {
            $hour = (int) $lt->hour_number;
            $row = ['jam_ke' => $hour, 'jam_mulai' => sprintf('%s - %s', $lt->formatted_start_time, $lt->formatted_end_time)];
            foreach ($days as $dayNum => $dayName) {
                $hasSchedule = $schedules
                    ->where('day_of_week', $dayNum)
                    ->where('hour_number', $hour)
                    ->first() !== null;

                $row[$dayNum] = $hasSchedule ? 1 : 0;
            }
            $grid[] = $row;
        }

        return $grid;
    }

    public function toggleSchedule($day, $hour)
    {
        if (!$this->selectedTeacherId) {
            Notification::make()
                ->warning()
                ->title('Pilih Guru Terlebih Dahulu')
                ->send();
            return;
        }

        $schedule = WeeklySchedules::where('teacher_id', $this->selectedTeacherId)
            ->where('day_of_week', $day)
            ->where('hour_number', $hour)
            ->first();

        if ($schedule) {
            $schedule->delete();
            Notification::make()
                ->success()
                ->title('Jadwal Dihapus')
                ->body("Jam Ke-{$hour} dihapus dari jadwal")
                ->send();
        } else {
            $scheduleTime = ScheduleTime::where('is_lesson', true)->where('hour_number', $hour)->first();

            WeeklySchedules::create([
                'teacher_id' => $this->selectedTeacherId,
                'day_of_week' => $day,
                'hour_number' => $hour,
                'schedule_time_id' => $scheduleTime?->id,
            ]);

            Notification::make()
                ->success()
                ->title('Jadwal Ditambahkan')
                ->body("Jam Ke-{$hour} ditambahkan ke jadwal")
                ->send();
        }

        $this->dispatch('refresh');
    }
}
