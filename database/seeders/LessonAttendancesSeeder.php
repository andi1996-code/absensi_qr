<?php

namespace Database\Seeders;

use App\Models\LessonAttendances;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LessonAttendancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat data absensi pelajaran untuk 5 hari terakhir
     * Dengan attendance rate yang bervariasi (60-95%)
     */
    public function run(): void
    {
        $teachers = Teachers::where('is_active', true)->get();

        // 5 hari terakhir (mundur dari hari ini, skip weekend)
        $dates = $this->getLastFiveLessonDays();

        foreach ($teachers as $teacher) {
            foreach ($dates as $date) {
                // Tentukan hari dalam minggu (1-6, Senin-Sabtu)
                $dayOfWeek = $date->dayOfWeekIso;

                // Skip jika bukan hari kerja (Sunday = 7)
                if ($dayOfWeek > 6) {
                    continue;
                }

                // Ambil jadwal guru di hari tersebut
                $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->orderBy('hour_number')
                    ->get();

                if ($schedules->isEmpty()) {
                    continue;
                }

                // Attendance rate: 60-95% (beberapa guru ada yang tidak hadir)
                $attendanceRate = rand(60, 95) / 100;

                foreach ($schedules as $schedule) {
                    // Random apakah guru absen di jam ini atau tidak
                    if (rand(1, 100) / 100 <= $attendanceRate) {
                        // Cek apakah sudah ada record
                        $exists = LessonAttendances::where('teacher_id', $teacher->id)
                            ->where('date', $date->toDateString())
                            ->where('hour_number', $schedule->hour_number)
                            ->exists();

                        if (!$exists) {
                            // Random waktu scan (dalam jam pelajaran tersebut)
                            // Misal jam ke-1 = 08:00-08:45, scan antara 08:00-08:45
                            $durationMinutes = 45; // Default, bisa diambil dari DurationSetting

                            $startMinutes = ($schedule->hour_number - 1) * $durationMinutes;
                            $startHour = 8 + intdiv($startMinutes, 60);
                            $startMinute = $startMinutes % 60;

                            $randomMinutesInHour = rand(0, $durationMinutes - 1);
                            $scanTime = $date->copy()
                                ->setHour($startHour)
                                ->setMinute($startMinute)
                                ->addMinutes($randomMinutesInHour);

                            LessonAttendances::create([
                                'teacher_id' => $teacher->id,
                                'date' => $date->toDateString(),
                                'hour_number' => $schedule->hour_number,
                                'scanned_at' => $scanTime,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('Seeder LessonAttendances berhasil dibuat!');
    }

    /**
     * Get 5 hari terakhir yang merupakan hari kerja (Senin-Sabtu)
     */
    private function getLastFiveLessonDays(): array
    {
        $dates = [];
        $current = now();

        while (count($dates) < 5) {
            // Cek jika hari kerja (dayOfWeekIso: 1=Senin, 7=Minggu)
            if ($current->dayOfWeekIso <= 6) {
                $dates[] = $current->copy();
            }

            $current->subDay();
        }

        return array_reverse($dates);
    }
}
