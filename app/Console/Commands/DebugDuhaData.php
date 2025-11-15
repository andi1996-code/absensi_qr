<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DuhaAttendances;

class DebugDuhaData extends Command
{
    protected $signature = 'debug:duha';
    protected $description = 'Debug Duha attendance data';

    public function handle()
    {
        $attendances = DuhaAttendances::with('teacher')->orderBy('scanned_at')->get();

        $this->info("Total records: " . $attendances->count());
        $this->newLine();

        foreach ($attendances as $attendance) {
            $time = $attendance->scanned_at->format('H:i:s');
            $late = $attendance->is_late;
            $status = $late > 0 ? "TERLAMBAT {$late} menit" : "TEPAT WAKTU";

            $this->line("{$attendance->teacher->name} | {$time} | is_late={$late} | {$status}");
        }

        return 0;
    }
}
