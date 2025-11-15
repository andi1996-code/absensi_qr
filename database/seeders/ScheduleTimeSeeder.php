<?php

namespace Database\Seeders;

use App\Models\ScheduleTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleTimeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            // Jam pelajaran
            [
                'hour_number' => 1,
                'start_time' => '08:00',
                'end_time' => '08:35',
                'label' => 'Jam ke 1',
                'is_lesson' => true,
            ],
            [
                'hour_number' => 2,
                'start_time' => '08:35',
                'end_time' => '09:10',
                'label' => 'Jam ke 2',
                'is_lesson' => true,
            ],
            [
                'hour_number' => 3,
                'start_time' => '09:10',
                'end_time' => '09:45',
                'label' => 'Jam ke 3',
                'is_lesson' => true,
            ],
            // Istirahat
            [
                'hour_number' => 4,
                'start_time' => '09:45',
                'end_time' => '10:00',
                'label' => 'Istirahat',
                'is_lesson' => false,
            ],
            // Jam pelajaran lanjutan
            [
                'hour_number' => 5,
                'start_time' => '10:00',
                'end_time' => '10:35',
                'label' => 'Jam ke 4',
                'is_lesson' => true,
            ],
            [
                'hour_number' => 6,
                'start_time' => '10:35',
                'end_time' => '11:10',
                'label' => 'Jam ke 5',
                'is_lesson' => true,
            ],
            [
                'hour_number' => 7,
                'start_time' => '11:10',
                'end_time' => '11:45',
                'label' => 'Jam ke 6',
                'is_lesson' => true,
            ],
            // Solat Dzuhur
            [
                'hour_number' => 8,
                'start_time' => '11:45',
                'end_time' => '12:25',
                'label' => 'Solat Dzuhur',
                'is_lesson' => false,
            ],
            // Jam pelajaran terakhir
            [
                'hour_number' => 9,
                'start_time' => '12:25',
                'end_time' => '13:00',
                'label' => 'Jam ke 7',
                'is_lesson' => true,
            ],
            [
                'hour_number' => 10,
                'start_time' => '13:00',
                'end_time' => '13:35',
                'label' => 'Jam ke 8',
                'is_lesson' => true,
            ],
            [
                'hour_number' => 11,
                'start_time' => '13:35',
                'end_time' => '14:10',
                'label' => 'Jam ke 9',
                'is_lesson' => true,
            ],
        ];

        foreach ($schedules as $schedule) {
            ScheduleTime::create($schedule);
            $this->command->info("Created: {$schedule['label']} ({$schedule['start_time']} - {$schedule['end_time']})");
        }

        $this->command->info("\n✅ Seeder ScheduleTime selesai!");
    }
}
