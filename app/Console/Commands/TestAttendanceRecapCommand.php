<?php

namespace App\Console\Commands;

use App\Models\Teachers;
use App\Models\LessonAttendances;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestAttendanceRecapCommand extends Command
{
    protected $signature = 'test:recap {--teacher=1}';
    protected $description = 'Test attendance recap filter';

    public function handle()
    {
        $teacherId = $this->option('teacher');
        $teacher = Teachers::find($teacherId);

        if (!$teacher) {
            $this->error("Teacher not found");
            return;
        }

        $this->info("Testing filter for: {$teacher->name} (ID: {$teacherId})");

        $date = Carbon::now();
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $this->line("Date range: {$startDate->toDateString()} to {$endDate->toDateString()}");

        // Test query dengan teacher_id
        $query = Teachers::where('id', $teacherId);
        $this->line("\nQuery count: " . $query->count());
        $this->line("Teacher: " . $query->first()->name);

        // Test lesson attendances
        $lessons = LessonAttendances::where('teacher_id', $teacherId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $this->info("\nLesson Attendances for {$teacher->name}: " . $lessons->count());

        foreach ($lessons->take(5) as $lesson) {
            $this->line("  - {$lesson->date}: Hour {$lesson->hour_number}");
        }

        // Test query tanpa filter
        $allLessons = LessonAttendances::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $this->info("\nAll Lesson Attendances: " . $allLessons->count());

        // Test teacher query
        $teacherQuery = $teacherId ? Teachers::where('id', $teacherId) : Teachers::where('is_active', true);
        $this->info("\nTeacher query result count: " . $teacherQuery->count());
        foreach ($teacherQuery->get() as $t) {
            $this->line("  - {$t->name} (ID: {$t->id})");
        }
    }
}
