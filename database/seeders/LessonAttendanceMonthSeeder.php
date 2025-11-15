<?php

namespace Database\Seeders;

use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Models\LessonAttendances;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LessonAttendanceMonthSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * Membuat attendance record untuk jadwal pelajaran selama 1 bulan.
     * Asumsi: 90% kehadiran (beberapa hari guru tidak hadir)
     *
     * Untuk 3 guru dengan:
     * - Budi Santoso: 5 jam/minggu = ~22 jam/bulan, ~20 hadir
     * - Siti Nurhaliza: 6 jam/minggu = ~26 jam/bulan, ~23 hadir
     * - Ahmad Wijaya: 7 jam/minggu = ~30 jam/bulan, ~27 hadir
     * Total ~70 attendance records
     */
    public function run(): void
    {
        $teachers = Teachers::where('is_active', true)->limit(3)->get();

        if ($teachers->count() < 3) {
            $this->command->error('Minimal harus ada 3 guru aktif.');
            return;
        }

        // Dapatkan jadwal mingguan yang sudah dibuat
        $schedules = WeeklySchedules::whereIn('teacher_id', $teachers->pluck('id'))->get();

        if ($schedules->count() === 0) {
            $this->command->error('Jadwal mingguan belum ada. Jalankan WeeklySchedulesTestSeeder terlebih dahulu.');
            return;
        }

        // Mulai dari awal bulan ini
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $attendanceCount = 0;
        $skippedCount = 0;

        // Looping setiap tanggal dalam sebulan
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip hari Minggu (day_of_week 7 atau 0)
            $dayOfWeek = $date->dayOfWeek;
            if ($dayOfWeek === 0) { // 0 = Minggu
                continue;
            }

            // Konversi Carbon dayOfWeek (0=Sunday) ke format database (1=Senin...6=Sabtu)
            $dbDayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;

            // Cari jadwal untuk hari ini
            $todaySchedules = $schedules->where('day_of_week', $dbDayOfWeek);

            foreach ($todaySchedules as $schedule) {
                // Simulasi: 90% kehadiran (10% tidak hadir/izin)
                $attendanceRate = rand(1, 100);

                if ($attendanceRate <= 90) {
                    // Guru hadir - record attendance
                    // Waktu scanning random antara jam 06:00 - 12:00
                    $scanHour = rand(6, 11);
                    $scanMinute = rand(0, 59);

                    $scannedAt = $date->copy()
                        ->setHour($scanHour)
                        ->setMinute($scanMinute)
                        ->setSecond(rand(0, 59));

                    try {
                        LessonAttendances::updateOrCreate(
                            [
                                'teacher_id' => $schedule->teacher_id,
                                'date' => $date->format('Y-m-d'),
                                'hour_number' => $schedule->hour_number,
                            ],
                            [
                                'scanned_at' => $scannedAt,
                            ]
                        );
                        $attendanceCount++;
                    } catch (\Exception $e) {
                        // Abaikan jika sudah ada (unique constraint)
                        $skippedCount++;
                    }
                }
            }
        }

        $this->command->info("\n✓ Attendance records berhasil dibuat:");
        $this->command->info("  - Total records: {$attendanceCount}");
        $this->command->info("  - Skipped (duplicate): {$skippedCount}");
        $this->command->info("  - Periode: {$startDate->format('d-m-Y')} s/d {$endDate->format('d-m-Y')}");
        $this->command->info("  - Tingkat kehadiran: ~90%");
    }
}
