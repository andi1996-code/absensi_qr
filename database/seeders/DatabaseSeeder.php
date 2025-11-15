<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed Duration Settings (harus duluan karena digunakan di seeder lain)
        $this->call([
            DurationSettingSeeder::class,
            ScheduleTimeSeeder::class,
            PositionSalarySeeder::class,
            TeachersSeeder::class,
            WeeklySchedulesTestSeeder::class,
            LessonAttendanceMonthSeeder::class,
            DuhaAttendancesSeeder::class,
            DepartureAttendancesSeeder::class,
        ]);
    }
}
