<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Models\ScheduleTime;
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
    public ?int $selectedClassRoomId = null;

    public bool $showClassModal = false;
    public ?int $pendingDay = null;
    public ?int $pendingHour = null;
    public ?int $modalClassRoomId = null;

    public function mount(): void
    {
        // Set selected teacher from query param if provided
        $teacherId = (int) request()->query('teacher_id', 0);
        $this->selectedTeacherId = $teacherId > 0 ? $teacherId : null;
        $classRoomId = (int) request()->query('class_room_id', 0);
        $this->selectedClassRoomId = $classRoomId > 0 ? $classRoomId : null;
    }

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

    public function getClassRooms()
    {
        return \App\Models\ClassRooms::orderBy('name')->get();
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

        // Get configured lesson times (user defined)
        $lessonTimes = ScheduleTime::where('is_lesson', true)
            ->orderBy('hour_number')
            ->get();

        // Group existing schedules by hour_number for quick lookup
        $schedules = WeeklySchedules::where('teacher_id', $this->selectedTeacherId)
            ->get()
            ->groupBy('hour_number');

        $matrix = [];
        foreach ($lessonTimes as $lt) {
            $hour = (int) $lt->hour_number;
            $row = [
                'jam_ke' => $hour,
                'jam_mulai' => sprintf('%s - %s', $lt->formatted_start_time, $lt->formatted_end_time),
                'days' => [],
                'schedule_time_id' => $lt->id,
                'label' => $lt->label,
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

    $scheduleTimeForHour = ScheduleTime::where('is_lesson', true)->where('hour_number', $hour)->first();
    $labelForHour = $scheduleTimeForHour?->label ?? 'Jam ' . $hour;

    if ($schedule) {
            $schedule->delete();
            Notification::make()
                ->success()
                ->title('Jadwal Dihapus')
        ->body("{$teacher->name} - {$days[$day]} {$labelForHour}")
                ->send();
        } else {
            // Tampilkan modal untuk pilih kelas
            $this->pendingDay = $day;
            $this->pendingHour = $hour;
            $this->modalClassRoomId = null;
            $this->showClassModal = true;
            return;
            // Find the corresponding ScheduleTime based on hour_number
            $scheduleTime = ScheduleTime::where('is_lesson', true)
                ->where('hour_number', $hour)
                ->first();

            if (! $scheduleTime) {
                $scheduleTime = ScheduleTime::where('is_lesson', true)->first();
            }

            $classRoomId = $this->selectedClassRoomId;
            $classRoomName = null;
            if ($classRoomId) {
                $classRoom = \App\Models\ClassRooms::find($classRoomId);
                $classRoomName = $classRoom?->name;
            }

            WeeklySchedules::create([
                'teacher_id' => $this->selectedTeacherId,
                'day_of_week' => $day,
                'hour_number' => $hour,
                'schedule_time_id' => $scheduleTime?->id ?? $scheduleTimeForHour?->id ?? 1,
                'class_room_id' => $classRoomId,
                'class_room' => $classRoomName,
            ]);

            Notification::make()
                ->success()
                ->title('Jadwal Ditambahkan')
                ->body("{$teacher->name} - {$days[$day]} {$labelForHour}" . ($classRoomName ? " ({$classRoomName})" : ''))
                ->send();
        }
    }

    public function saveScheduleWithClass(): void
    {
        if (!$this->modalClassRoomId) {
            Notification::make()
                ->warning()
                ->title('Pilih Kelas Terlebih Dahulu')
                ->send();
            return;
        }

        if (!$this->pendingDay || !$this->pendingHour) {
            return;
        }

        $teacher = Teachers::find($this->selectedTeacherId);
        $classRoom = \App\Models\ClassRooms::find($this->modalClassRoomId);

        $scheduleTime = ScheduleTime::where('is_lesson', true)
            ->where('hour_number', $this->pendingHour)
            ->first();

        if (!$scheduleTime) {
            $scheduleTime = ScheduleTime::where('is_lesson', true)->first();
        }

        WeeklySchedules::create([
            'teacher_id' => $this->selectedTeacherId,
            'day_of_week' => $this->pendingDay,
            'hour_number' => $this->pendingHour,
            'schedule_time_id' => $scheduleTime?->id ?? 1,
            'class_room_id' => $this->modalClassRoomId,
            'class_room' => $classRoom?->name,
        ]);

        $days = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];

        $scheduleTimeForHour = ScheduleTime::where('is_lesson', true)->where('hour_number', $this->pendingHour)->first();
        $labelForHour = $scheduleTimeForHour?->label ?? 'Jam ' . $this->pendingHour;

        Notification::make()
            ->success()
            ->title('Jadwal Ditambahkan')
            ->body("{$teacher->name} - {$days[$this->pendingDay]} {$labelForHour} ({$classRoom->name})")
            ->send();

        // Reset modal
        $this->showClassModal = false;
        $this->pendingDay = null;
        $this->pendingHour = null;
        $this->modalClassRoomId = null;
    }

    public function cancelClassModal(): void
    {
        $this->showClassModal = false;
        $this->pendingDay = null;
        $this->pendingHour = null;
        $this->modalClassRoomId = null;
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
