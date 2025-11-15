<?php

namespace Database\Seeders;

use App\Models\PositionSalary;
use Illuminate\Database\Seeder;

class PositionSalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salaries = [
            [
                'position' => 'Kepala Sekolah',
                'salary_adjustment' => 500000,
                'description' => 'Tunjangan untuk Kepala Sekolah',
                'is_active' => true,
            ],
            [
                'position' => 'Wakil Kepala',
                'salary_adjustment' => 300000,
                'description' => 'Tunjangan untuk Wakil Kepala Sekolah',
                'is_active' => true,
            ],
            [
                'position' => 'Guru Kelas',
                'salary_adjustment' => 100000,
                'description' => 'Tunjangan untuk Guru Kelas',
                'is_active' => true,
            ],
            [
                'position' => 'Guru Mapel',
                'salary_adjustment' => 75000,
                'description' => 'Tunjangan untuk Guru Mata Pelajaran',
                'is_active' => true,
            ],
        ];

        foreach ($salaries as $salary) {
            PositionSalary::updateOrCreate(
                ['position' => $salary['position']],
                $salary
            );
        }
    }
}
