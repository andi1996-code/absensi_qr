<?php

namespace App\Console\Commands;

use App\Models\LessonAttendances;
use App\Models\Teachers;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DebugAttendanceFilterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:attendance-filter {--teacher=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug attendance filter by teacher';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teacherId = $this->option('teacher');
        $teacher = Teachers::find($teacherId);

        if (!$teacher) {
            $this->error("Teacher {$teacherId} not found");
            return;
        }

        $this->info("=== DEBUG ATTENDANCE FILTER ===\n");
        $this->info("Teacher: {$teacher->name} (ID: {$teacher->id})\n");

        // Test monthly filter for October 2025
        $startDate = Carbon::create(2025, 10, 1)->toDateString();
        $endDate = Carbon::create(2025, 10, 31)->toDateString();

        $this->line("Date range: {$startDate} to {$endDate}\n");

        // Query semua guru
        $allQuery = LessonAttendances::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->limit(5);

        $allCount = LessonAttendances::whereBetween('date', [$startDate, $endDate])->count();
        $this->info("Total records (all teachers): {$allCount}");
        foreach ($allQuery->get() as $record) {
            $t = Teachers::find($record->teacher_id);
            $this->line("  - {$record->date}: {$t->name} (Hour {$record->hour_number})");
        }

        // Query for specific teacher
        $this->info("\nRecords for {$teacher->name}:");
        $specificQuery = LessonAttendances::where('teacher_id', $teacherId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc');

        $count = $specificQuery->count();
        $this->line("Total records: {$count}");

        foreach ($specificQuery->limit(10)->get() as $record) {
            $this->line("  - {$record->date}: Hour {$record->hour_number}");
        }

        if ($count > 10) {
            $this->line("  ... and " . ($count - 10) . " more");
        }
    }
}
