<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Models\Teachers;
use App\Models\WeeklySchedules;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class ScheduleBuilderWeeklySchedules extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.resources.weekly-schedules-resource.pages.schedule-builder';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jadwal Grid';

    protected static ?string $navigationParent = 'WeeklySchedulesResource';

    protected static ?string $title = 'Jadwal Guru (Tabel Grid)';

    public ?int $selectedTeacherId = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali ke Jadwal')
                ->url('/admin/weekly-schedules')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function getTeachers()
    {
        return Teachers::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getScheduleMatrix()
    {
        if (!$this->selectedTeacherId) {
            return [];
        }

        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $schedules = WeeklySchedules::where('teacher_id', $this->selectedTeacherId)
            ->get()
            ->groupBy('hour_number');

        $matrix = [];
        for ($hour = 1; $hour <= 8; $hour++) {
            $row = [
                'jam_ke' => $hour,
                'jam_mulai' => $this->getTimeRange($hour),
                'days' => [],
            ];

            foreach ($days as $dayNum => $dayName) {
                $hasSchedule = isset($schedules[$hour]) &&
                    $schedules[$hour]->where('day_of_week', $dayNum)->first() !== null;

                $row['days'][$dayNum] = [
                    'day_name' => $dayName,
                    'day_num' => $dayNum,
                    'has_schedule' => $hasSchedule,
                ];
            }

            $matrix[] = $row;
        }

        return $matrix;
    }

    public function getTimeRange(int $hour): string
    {
        $durationMinutes = 45;
        $baseHour = 8;

        $startMinutes = ($hour - 1) * $durationMinutes;
        $startHour = $baseHour + intdiv($startMinutes, 60);
        $startMin = $startMinutes % 60;

        $endMinutes = $hour * $durationMinutes;
        $endHour = $baseHour + intdiv($endMinutes, 60);
        $endMin = $endMinutes % 60;

        return sprintf('%02d:%02d - %02d:%02d', $startHour, $startMin, $endHour, $endMin);
    }

    public function toggleSchedule(int $day, int $hour): void
    {
        if (!$this->selectedTeacherId) {
            Notification::make()
                ->warning()
                ->title('Pilih Guru Terlebih Dahulu')
                ->send();
            return;
        }

        $teacher = Teachers::find($this->selectedTeacherId);
        if (!$teacher) {
            Notification::make()
                ->danger()
                ->title('Guru Tidak Ditemukan')
                ->send();
            return;
        }

        $schedule = WeeklySchedules::where('teacher_id', $this->selectedTeacherId)
            ->where('day_of_week', $day)
            ->where('hour_number', $hour)
            ->first();

        $days = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];

        if ($schedule) {
            $schedule->delete();
            Notification::make()
                ->success()
                ->title('Jadwal Dihapus')
                ->body("{$teacher->name} - {$days[$day]} Jam Ke-{$hour}")
                ->send();
        } else {
            // Find the corresponding ScheduleTime based on hour_number
            $scheduleTime = \App\Models\ScheduleTime::where('is_lesson', true)
                ->get()
                ->first(function ($st) use ($hour) {
                    preg_match('/\d+/', $st->label, $matches);
                    return !empty($matches) && (int)$matches[0] === $hour;
                });

            WeeklySchedules::create([
                'teacher_id' => $this->selectedTeacherId,
                'day_of_week' => $day,
                'hour_number' => $hour,
                'schedule_time_id' => $scheduleTime?->id ?? 1,
            ]);

            Notification::make()
                ->success()
                ->title('Jadwal Ditambahkan')
                ->body("{$teacher->name} - {$days[$day]} Jam Ke-{$hour}")
                ->send();
        }
    }

    public function getTotalScheduledHours(): int
    {
        if (!$this->selectedTeacherId) {
            return 0;
        }

        return WeeklySchedules::where('teacher_id', $this->selectedTeacherId)
            ->count();
    }
}
