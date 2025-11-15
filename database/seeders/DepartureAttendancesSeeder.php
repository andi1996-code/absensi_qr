<?php

namespace Database\Seeders;

use App\Models\DepartureAttendances;
use App\Models\Teachers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DepartureAttendancesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = today();
        $teachers = Teachers::where('is_active', true)->get();

        foreach ($teachers as $teacher) {
            // Generate scan times antara 14:10 - 15:30
            // 70% scan tepat waktu (14:10 - 15:00)
            // 30% pulang awal (13:30 - 14:09) - untuk testing
            $randomDecision = rand(1, 100);

            if ($randomDecision <= 70) {
                // Scan tepat waktu: 14:10 - 15:00
                $randomHour = 14;
                $randomMinute = rand(10, 60);
            } else {
                // Scan awal: 13:30 - 14:09 (untuk testing pulang awal)
                $randomHour = 13;
                $randomMinute = rand(30, 59);
            }

            $scannedAt = $today->copy()->setTime($randomHour, $randomMinute, rand(0, 59));

            // Hitung is_late (pulang awal dari jam 14:10)
            $departureNormalTime = $today->copy()->setTime(14, 10, 0);
            $pulangAwal = 0;

            if ($scannedAt->lessThan($departureNormalTime)) {
                $pulangAwal = (int) $departureNormalTime->diffInMinutes($scannedAt);
            }

            DepartureAttendances::create([
                'teacher_id' => $teacher->id,
                'date' => $today,
                'scanned_at' => $scannedAt,
                'is_late' => $pulangAwal, // 0 jika normal/tidak awal, >0 jika awal
            ]);
        }
    }
}
