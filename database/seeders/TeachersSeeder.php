<?php

namespace Database\Seeders;

use App\Models\Teachers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeachersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'Budi Santoso',
                'nip' => '198501121990011001',
                'qr_code' => 'QR_GURU_001',
                'email' => 'budi.santoso@school.com',
                'phone' => '081234567890',
                'position' => 'Kepala Sekolah',
                'photo_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'nip' => '198602152005012002',
                'qr_code' => 'QR_GURU_002',
                'email' => 'siti.nurhaliza@school.com',
                'phone' => '082345678901',
                'position' => 'Guru Kelas',
                'photo_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Ahmad Wijaya',
                'nip' => '198703201995022003',
                'qr_code' => 'QR_GURU_003',
                'email' => 'ahmad.wijaya@school.com',
                'phone' => '083456789012',
                'position' => 'Guru Mapel',
                'photo_path' => null,
                'is_active' => true,
            ],
        ];

        foreach ($teachers as $teacher) {
            Teachers::updateOrCreate(
                ['qr_code' => $teacher['qr_code']],
                $teacher
            );
        }
    }
}
