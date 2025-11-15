<?php

namespace Database\Seeders;

use App\Models\LessonAttendances;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LessonAttendancesSingleTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat data absensi pelajaran untuk 1 guru selama 1 bulan (Oktober 2025)
     * Untuk testing penggajian berdasarkan attendance rate
     */
    public function run(): void
    {
        // Pilih guru pertama yang aktif
        $teacher = Teachers::where('is_active', true)->first();

        if (!$teacher) {
            $this->command->error('Tidak ada guru aktif!');
            return;
        }

        $this->command->info("Membuat data absensi 1 bulan untuk: {$teacher->name}");

        // Range: 1 Oktober 2025 - 31 Oktober 2025
        $startDate = Carbon::create(2025, 10, 1);
        $endDate = Carbon::create(2025, 10, 31);

        $totalAttendance = 0;
        $totalExpected = 0;

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Skip jika hari Minggu (dayOfWeekIso = 7)
            if ($currentDate->dayOfWeekIso > 6) {
                $currentDate->addDay();
                continue;
            }

            $dayOfWeek = $currentDate->dayOfWeekIso;

            // Ambil jadwal guru di hari tersebut
            $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('hour_number')
                ->get();

            if ($schedules->isNotEmpty()) {
                // Attendance rate: 85% (konsisten untuk testing penggajian)
                $attendanceRate = 0.85;

                foreach ($schedules as $schedule) {
                    $totalExpected++;

                    // Cek apakah guru hadir (85% hadir)
                    if (rand(1, 100) / 100 <= $attendanceRate) {
                        // Cek duplikat
                        $exists = LessonAttendances::where('teacher_id', $teacher->id)
                            ->where('date', $currentDate->toDateString())
                            ->where('hour_number', $schedule->hour_number)
                            ->exists();

                        if (!$exists) {
                            // Random waktu scan dalam jam pelajaran
                            $durationMinutes = 45; // Default

                            $startMinutes = ($schedule->hour_number - 1) * $durationMinutes;
                            $startHour = 8 + intdiv($startMinutes, 60);
                            $startMinute = $startMinutes % 60;

                            $randomMinutesInHour = rand(0, $durationMinutes - 1);
                            $scanTime = $currentDate->copy()
                                ->setHour($startHour)
                                ->setMinute($startMinute)
                                ->addMinutes($randomMinutesInHour);

                            LessonAttendances::create([
                                'teacher_id' => $teacher->id,
                                'date' => $currentDate->toDateString(),
                                'hour_number' => $schedule->hour_number,
                                'scanned_at' => $scanTime,
                            ]);

                            $totalAttendance++;
                        }
                    }
                }
            }

            $currentDate->addDay();
        }

        $attendancePercentage = $totalExpected > 0
            ? round(($totalAttendance / $totalExpected) * 100, 2)
            : 0;

        $this->command->info("✅ Seeding selesai!");
        $this->command->info("   Guru: {$teacher->name}");
        $this->command->info("   Periode: 1 Oktober - 31 Oktober 2025");
        $this->command->info("   Total Hadir: {$totalAttendance} jam");
        $this->command->info("   Total Diharapkan: {$totalExpected} jam");
        $this->command->info("   Attendance Rate: {$attendancePercentage}%");
    }
}
