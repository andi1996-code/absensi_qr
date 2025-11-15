<?php

namespace App\Console\Commands;

use App\Models\DuhaAttendances;
use App\Models\LessonAttendances;
use Illuminate\Console\Command;

class CheckAttendanceDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check attendance data in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== CHECK ATTENDANCE DATA ===\n");

        $duhaCount = DuhaAttendances::whereMonth('date', 10)->whereYear('date', 2025)->count();
        $lessonCount = LessonAttendances::whereMonth('date', 10)->whereYear('date', 2025)->count();

        $this->info("October 2025:");
        $this->line("  - Duha Attendances: {$duhaCount}");
        $this->line("  - Lesson Attendances: {$lessonCount}");

        $allDuha = DuhaAttendances::count();
        $allLesson = LessonAttendances::count();

        $this->info("\nTotal in Database:");
        $this->line("  - Duha Attendances: {$allDuha}");
        $this->line("  - Lesson Attendances: {$allLesson}");

        // Show sample data
        $this->info("\nSample Lesson Attendance Data:");
        $lessons = LessonAttendances::limit(5)->orderBy('date', 'desc')->get();
        foreach ($lessons as $lesson) {
            $this->line("  - {$lesson->date}: Teacher {$lesson->teacher_id}, Hour {$lesson->hour_number}");
        }
    }
}
