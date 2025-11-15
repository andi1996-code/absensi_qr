<?php

namespace Database\Seeders;

use App\Models\DuhaAttendances;
use App\Models\Teachers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DuhaAttendancesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama untuk hari ini
        DuhaAttendances::whereDate('date', today())->delete();

        $today = today();
        $duhaMaxTime = Carbon::parse($today)->setTime(8, 0, 0); // Max time 08:00
        $teachers = Teachers::where('is_active', true)->get();

        if ($teachers->isEmpty()) {
            $this->command->error('Tidak ada guru aktif di database!');
            return;
        }

        // Scenario 1: Guru tepat waktu (sebelum jam 8)
        $onTimeScenarios = [
            ['hour' => 6, 'minute' => 29, 'second' => 2],   // 06:29:02
            ['hour' => 6, 'minute' => 50, 'second' => 29],  // 06:50:29
            ['hour' => 6, 'minute' => 52, 'second' => 16],  // 06:52:16
            ['hour' => 7, 'minute' => 36, 'second' => 4],   // 07:36:04
            ['hour' => 7, 'minute' => 19, 'second' => 10],  // 07:19:10
        ];

        // Scenario 2: Guru terlambat (setelah jam 8)
        $lateScenarios = [
            ['hour' => 8, 'minute' => 15, 'second' => 5],   // 08:15:05 - Terlambat 15 menit
            ['hour' => 8, 'minute' => 36, 'second' => 23],  // 08:36:23 - Terlambat 36 menit
            ['hour' => 8, 'minute' => 49, 'second' => 19],  // 08:49:19 - Terlambat 49 menit
            ['hour' => 9, 'minute' => 5, 'second' => 10],   // 09:05:10 - Terlambat 65 menit
        ];

        $index = 0;

        foreach ($teachers as $teacher) {
            // Tentukan apakah guru ini on-time atau late
            if ($index < 5 && $index < count($onTimeScenarios)) {
                // Tepat waktu
                $scenario = $onTimeScenarios[$index];
                $scannedAt = Carbon::parse($today)
                    ->setTime($scenario['hour'], $scenario['minute'], $scenario['second']);

                $lateMinutes = 0; // Tepat waktu
            } elseif ($index >= 5 && ($index - 5) < count($lateScenarios)) {
                // Terlambat
                $scenario = $lateScenarios[$index - 5];
                $scannedAt = Carbon::parse($today)
                    ->setTime($scenario['hour'], $scenario['minute'], $scenario['second']);

                // Hitung keterlambatan dengan metode yang benar
                if ($scannedAt->greaterThan($duhaMaxTime)) {
                    $diffInSeconds = $duhaMaxTime->diffInSeconds($scannedAt);
                    $lateMinutes = (int) ceil($diffInSeconds / 60);
                } else {
                    $lateMinutes = 0;
                }
            } else {
                // Guru sisanya random antara on-time dan late
                if ($index % 2 == 0) {
                    // Tepat waktu - random antara 06:00 - 07:59
                    $scannedAt = Carbon::parse($today)
                        ->setTime(rand(6, 7), rand(0, 59), rand(0, 59));
                    $lateMinutes = 0;
                } else {
                    // Terlambat - random antara 08:01 - 09:30
                    $hour = 8;
                    $minute = rand(1, 90);
                    if ($minute > 59) {
                        $hour = 9;
                        $minute = $minute - 60;
                    }
                    $scannedAt = Carbon::parse($today)
                        ->setTime($hour, $minute, rand(0, 59));

                    if ($scannedAt->greaterThan($duhaMaxTime)) {
                        $diffInSeconds = $duhaMaxTime->diffInSeconds($scannedAt);
                        $lateMinutes = (int) ceil($diffInSeconds / 60);
                    } else {
                        $lateMinutes = 0;
                    }
                }
            }

            DuhaAttendances::create([
                'teacher_id' => $teacher->id,
                'date' => $today,
                'scanned_at' => $scannedAt,
                'is_late' => $lateMinutes,
            ]);

            $status = $lateMinutes > 0 ? "TERLAMBAT {$lateMinutes} menit" : "TEPAT WAKTU";
            $this->command->info("{$teacher->name} - {$scannedAt->format('H:i:s')} - {$status}");

            $index++;
        }

        $this->command->info("\n✅ Seeder selesai! Total {$index} absen duha dibuat.");
    }
}
