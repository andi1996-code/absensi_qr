<?php

namespace App\Console\Commands;

use App\Models\Teachers;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugSalaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:salary {--teacher=1} {--month=10} {--year=2025}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug salary calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teacherId = $this->option('teacher');
        $month = $this->option('month');
        $year = $this->option('year');

        $teacher = Teachers::find($teacherId);
        if (!$teacher) {
            $this->error("Teacher with ID {$teacherId} not found!");
            return;
        }

        $this->info("=== DEBUG SALARY CALCULATION ===\n");
        $this->info("Teacher: {$teacher->name} (ID: {$teacher->id})");
        $this->info("Period: {$month}/{$year}\n");

        // Check weekly schedules
        $schedules = DB::table('weekly_schedules')
            ->where('teacher_id', $teacher->id)
            ->get();

        $this->info("Weekly Schedules:");
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        foreach ($schedules as $schedule) {
            $this->line("  - {$days[$schedule->day_of_week]}: Jam {$schedule->hour_number}");
        }

        // Check attendance
        $attendances = DB::table('lesson_attendances')
            ->where('teacher_id', $teacher->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $this->info("\nAttendances in {$month}/{$year}:");
        $this->line("  Total: " . count($attendances));

        if (count($attendances) > 0) {
            foreach ($attendances->take(10) as $att) {
                $this->line("  - {$att->date}: Jam {$att->hour_number}");
            }
            if (count($attendances) > 10) {
                $this->line("  ... and " . (count($attendances) - 10) . " more");
            }
        }

        // Calculate using service
        $service = new SalaryCalculationService();
        $scheduled = $service->getTotalScheduledHours($teacher, $year, $month);
        $attended = $service->getTotalAttendedHours($teacher, $year, $month);

        $this->info("\nCalculation Result:");
        $this->line("  Scheduled Hours: {$scheduled}");
        $this->line("  Attended Hours: {$attended}");
        $this->line("  Absent Hours: " . ($scheduled - $attended));
    }
}
