<?php

namespace Database\Seeders;

use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Models\ScheduleTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeeklySchedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Aturan:
     * - Maksimal 9 jam pelajaran per minggu per guru (slot 1, 2, 3, 5, 6, 7, 9, 10, 11)
     * - Setiap guru dijadwalkan di beberapa hari dengan total jam 4-8
     * - Distribusi jam merata sepanjang minggu
     * - Hanya jam pelajaran (is_lesson = true), skip istirahat/solat
     */
    public function run(): void
    {
        $teachers = Teachers::where('is_active', true)->get();

        // Get only lesson schedule times (exclude istirahat dan solat)
        $lessonSchedules = ScheduleTime::where('is_lesson', true)->get();

        foreach ($teachers as $teacher) {
            // Random total jam per minggu: 4, 5, 6, 7, atau 8
            $totalHoursPerWeek = rand(4, 8);

            // Hari kerja: 1-6 (Senin-Sabtu)
            $workingDays = [1, 2, 3, 4, 5, 6];

            // Shuffle hari untuk distribusi acak
            shuffle($workingDays);

            $hoursAssigned = 0;

            // Distribusikan jam ke berbagai hari
            foreach ($workingDays as $dayOfWeek) {
                if ($hoursAssigned >= $totalHoursPerWeek) {
                    break;
                }

                // Tentukan berapa jam di hari ini (1-3 jam per hari)
                $hoursThisDay = min(
                    rand(1, 3),
                    $totalHoursPerWeek - $hoursAssigned
                );

                // Shuffle lesson schedules untuk random selection
                $randomSchedules = $lessonSchedules->shuffle()->take($hoursThisDay);

                // Buat record untuk setiap jam pada hari tersebut
                foreach ($randomSchedules as $schedule) {
                    // Cek apakah jam ini sudah terjadwal (unique constraint)
                    $exists = WeeklySchedules::where('teacher_id', $teacher->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('schedule_time_id', $schedule->id)
                        ->exists();

                    if (!$exists) {
                        WeeklySchedules::create([
                            'teacher_id' => $teacher->id,
                            'day_of_week' => $dayOfWeek,
                            'hour_number' => $schedule->hour_number,
                            'schedule_time_id' => $schedule->id,
                        ]);
                        $hoursAssigned++;
                    }
                }
            }

            // Log info
            $this->command->info("Guru {$teacher->name} dijadwalkan {$hoursAssigned} jam per minggu");
        }
    }
}
