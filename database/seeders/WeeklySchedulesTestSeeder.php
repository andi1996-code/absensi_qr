<?php

namespace Database\Seeders;

use App\Models\ScheduleTime;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeeklySchedulesTestSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * Membuat jadwal sederhana untuk 3 guru test:
     * - Budi Santoso: 5 jam/minggu di kelas A (Senin-Jumat)
     * - Siti Nurhaliza: 6 jam/minggu di kelas B (Senin-Rabu, Jumat-Sabtu)
     * - Ahmad Wijaya: 7 jam/minggu di kelas C (Senin-Sabtu)
     */
    public function run(): void
    {
        // Ambil 3 guru pertama yang aktif
        $teachers = Teachers::where('is_active', true)->limit(3)->get();

        if ($teachers->count() < 3) {
            $this->command->error('Minimal harus ada 3 guru aktif. Jalankan TeachersSeeder terlebih dahulu.');
            return;
        }

        // Jadwal untuk Budi Santoso (ID 1) - 5 jam/minggu
        $budiSchedule = [
            ['day_of_week' => 1, 'hour_number' => 1],  // Senin jam 1
            ['day_of_week' => 2, 'hour_number' => 2],  // Selasa jam 2
            ['day_of_week' => 3, 'hour_number' => 3],  // Rabu jam 3
            ['day_of_week' => 4, 'hour_number' => 1],  // Kamis jam 1
            ['day_of_week' => 5, 'hour_number' => 2],  // Jumat jam 2
        ];

        // Jadwal untuk Siti Nurhaliza (ID 2) - 6 jam/minggu
        $sitiSchedule = [
            ['day_of_week' => 1, 'hour_number' => 3],  // Senin jam 3
            ['day_of_week' => 2, 'hour_number' => 4],  // Selasa jam 4
            ['day_of_week' => 3, 'hour_number' => 5],  // Rabu jam 5
            ['day_of_week' => 5, 'hour_number' => 3],  // Jumat jam 3
            ['day_of_week' => 6, 'hour_number' => 1],  // Sabtu jam 1
            ['day_of_week' => 6, 'hour_number' => 2],  // Sabtu jam 2
        ];

        // Jadwal untuk Ahmad Wijaya (ID 3) - 7 jam/minggu
        $ahmadSchedule = [
            ['day_of_week' => 1, 'hour_number' => 5],  // Senin jam 5
            ['day_of_week' => 2, 'hour_number' => 6],  // Selasa jam 6
            ['day_of_week' => 3, 'hour_number' => 7],  // Rabu jam 7
            ['day_of_week' => 4, 'hour_number' => 3],  // Kamis jam 3
            ['day_of_week' => 4, 'hour_number' => 4],  // Kamis jam 4
            ['day_of_week' => 5, 'hour_number' => 5],  // Jumat jam 5
            ['day_of_week' => 6, 'hour_number' => 3],  // Sabtu jam 3
        ];

        // Insert jadwal Budi Santoso
        foreach ($budiSchedule as $schedule) {
            $scheduleTime = ScheduleTime::where('hour_number', $schedule['hour_number'])->first();
            WeeklySchedules::updateOrCreate(
                [
                    'teacher_id' => $teachers[0]->id,
                    'day_of_week' => $schedule['day_of_week'],
                    'hour_number' => $schedule['hour_number'],
                ],
                [
                    'schedule_time_id' => $scheduleTime ? $scheduleTime->id : null,
                    'class_room' => 'A', // Semua jadwal Budi di kelas A
                ]
            );
        }
        $this->command->info("✓ Jadwal {$teachers[0]->name}: 5 jam/minggu");

        // Insert jadwal Siti Nurhaliza
        foreach ($sitiSchedule as $schedule) {
            $scheduleTime = ScheduleTime::where('hour_number', $schedule['hour_number'])->first();
            WeeklySchedules::updateOrCreate(
                [
                    'teacher_id' => $teachers[1]->id,
                    'day_of_week' => $schedule['day_of_week'],
                    'hour_number' => $schedule['hour_number'],
                ],
                [
                    'schedule_time_id' => $scheduleTime ? $scheduleTime->id : null,
                    'class_room' => 'B', // Semua jadwal Siti di kelas B
                ]
            );
        }
        $this->command->info("✓ Jadwal {$teachers[1]->name}: 6 jam/minggu");

        // Insert jadwal Ahmad Wijaya
        foreach ($ahmadSchedule as $schedule) {
            $scheduleTime = ScheduleTime::where('hour_number', $schedule['hour_number'])->first();
            WeeklySchedules::updateOrCreate(
                [
                    'teacher_id' => $teachers[2]->id,
                    'day_of_week' => $schedule['day_of_week'],
                    'hour_number' => $schedule['hour_number'],
                ],
                [
                    'schedule_time_id' => $scheduleTime ? $scheduleTime->id : null,
                    'class_room' => 'C', // Semua jadwal Ahmad di kelas C
                ]
            );
        }
        $this->command->info("✓ Jadwal {$teachers[2]->name}: 7 jam/minggu");

        $this->command->info("\n✓ Total jadwal pelajaran: " . WeeklySchedules::count() . " jam");
    }
}
