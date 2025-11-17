<?php

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require_once 'vendor/autoload.php';

use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Livewire\ScannerPage;

echo "=== Testing Absensi Mengajar ===\n\n";

// Ambil guru pertama
$teacher = Teachers::first();
if (!$teacher) {
    echo "Tidak ada guru. Jalankan seeder dulu.\n";
    exit;
}

echo "Guru: {$teacher->name} (ID: {$teacher->id})\n\n";

// Test untuk setiap hari
$days = [
    1 => 'Senin',
    2 => 'Selasa',
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
    6 => 'Sabtu',
];

foreach ($days as $dayNum => $dayName) {
    $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
        ->where('day_of_week', $dayNum)
        ->get();

    if ($schedules->isEmpty()) {
        echo "{$dayName}: Tidak ada jadwal\n";
        continue;
    }

    // Simulasi ScannerPage
    $scannerPage = new ScannerPage();
    $reflection = new ReflectionClass($scannerPage);
    $method = $reflection->getMethod('getScheduleBlocks');
    $method->setAccessible(true);
    $blocks = $method->invoke($scannerPage, $schedules);

    echo "{$dayName}: " . count($schedules) . " jam\n";
    foreach ($blocks as $block) {
        echo "  - Sesi di {$block['class_room']}: jam " . implode(',', $block['hours']) . "\n";
    }
    echo "\n";
}

echo "=== Skenario Test Absensi ===\n";
echo "1. Scan di jam pertama sesi: Semua jam di sesi present\n";
echo "2. Scan di jam kedua sesi: Jam pertama absent, sisanya present\n";
echo "3. Scan di jam lain: Ditolak jika tidak ada sesi\n";
echo "4. Scan duplikat: Ditolak\n";
echo "\nTest selesai.\n";
