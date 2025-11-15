<?php

namespace Database\Seeders;

use App\Models\DurationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DurationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah sudah ada, jika tidak buat default 45 menit
        if (DurationSetting::count() === 0) {
            DurationSetting::create([
                'lesson_duration_minutes' => 45,
            ]);
        }
    }
}
