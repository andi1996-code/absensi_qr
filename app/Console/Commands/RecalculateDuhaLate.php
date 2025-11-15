<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DuhaAttendances;
use Carbon\Carbon;

class RecalculateDuhaLate extends Command
{
    protected $signature = 'attendance:recalculate-duha';
    protected $description = 'Recalculate late minutes for Duha attendances';

    public function handle()
    {
        $this->info('Recalculating Duha attendance late minutes...');

        $attendances = DuhaAttendances::all();
        $updated = 0;

        foreach ($attendances as $attendance) {
            $scannedAt = Carbon::parse($attendance->scanned_at);
            $scanDate = $scannedAt->copy()->startOfDay();
            $duhaMaxTime = $scanDate->copy()->setTime(8, 0, 0);

            $lateMinutes = 0;
            if ($scannedAt->greaterThan($duhaMaxTime)) {
                $diffInSeconds = $scannedAt->diffInSeconds($duhaMaxTime);
                $lateMinutes = (int) ceil($diffInSeconds / 60);
            }

            if ($attendance->is_late != $lateMinutes) {
                $oldLate = $attendance->is_late;
                $attendance->is_late = $lateMinutes;
                $attendance->save();

                $this->line("ID {$attendance->id}: {$scannedAt->format('Y-m-d H:i:s')} - Updated from {$oldLate} to {$lateMinutes} minutes");
                $updated++;
            }
        }

        $this->info("Done! Updated {$updated} records out of {$attendances->count()} total records.");

        return 0;
    }
}
