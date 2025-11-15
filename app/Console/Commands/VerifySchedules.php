<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleTime;
use App\Models\WeeklySchedules;

class VerifySchedules extends Command
{
    protected $signature = 'verify:schedules';
    protected $description = 'Verify schedule times and weekly schedules';

    public function handle()
    {
        $this->info('=== JADWAL JAM PELAJARAN ===');
        $this->newLine();

        $schedules = ScheduleTime::orderBy('hour_number')->get();
        foreach ($schedules as $schedule) {
            $type = $schedule->is_lesson ? '📚' : '⏸️';
            $this->line("{$type} Slot {$schedule->hour_number}: {$schedule->label} ({$schedule->start_time} - {$schedule->end_time})");
        }

        $this->newLine();
        $this->info('=== SAMPEL JADWAL MINGGUAN ===');
        $this->newLine();

        $samples = WeeklySchedules::with(['teacher', 'scheduleTime'])
            ->limit(20)
            ->get();

        foreach ($samples as $schedule) {
            $teacher = $schedule->teacher->name;
            $day = $this->getDayName($schedule->day_of_week);
            $time = $schedule->scheduleTime ? "{$schedule->scheduleTime->start_time} - {$schedule->scheduleTime->end_time}" : 'N/A';
            $label = $schedule->scheduleTime ? $schedule->scheduleTime->label : 'N/A';

            $this->line("{$teacher} | {$day} | {$label} ({$time})");
        }

        return 0;
    }

    private function getDayName($dayOfWeek)
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        return $days[$dayOfWeek] ?? 'Unknown';
    }
}
