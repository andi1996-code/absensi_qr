<?php

namespace Tests\Feature;

use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Models\LessonAttendances;
use App\Livewire\ScannerPage;
use Tests\TestCase;
use ReflectionClass;
use App\Models\ScheduleTime;
use Carbon\Carbon;

class AbsensiMengajarTest extends TestCase
{
    /**
     * Test deteksi sesi jadwal
     */
    public function test_get_schedule_blocks()
    {
        // Setup ScheduleTime
        ScheduleTime::updateOrCreate(
            ['hour_number' => 1],
            [
                'start_time' => '08:00',
                'end_time' => '08:35',
                'label' => 'Jam ke 1',
                'is_lesson' => true,
            ]
        );
        ScheduleTime::updateOrCreate(
            ['hour_number' => 2],
            [
                'start_time' => '08:35',
                'end_time' => '09:10',
                'label' => 'Jam ke 2',
                'is_lesson' => true,
            ]
        );
        ScheduleTime::updateOrCreate(
            ['hour_number' => 4],
            [
                'start_time' => '10:00',
                'end_time' => '10:35',
                'label' => 'Jam ke 4',
                'is_lesson' => true,
            ]
        );

        // Buat guru
        $teacher = Teachers::factory()->create();

        // Buat jadwal: jam 1,2,4 di kelas A
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 1,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 2,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 4,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );

        $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
            ->where('day_of_week', 1)
            ->get();

        $scannerPage = new ScannerPage();
        $reflection = new ReflectionClass($scannerPage);
        $method = $reflection->getMethod('getScheduleBlocks');
        $method->setAccessible(true);
        $blocks = $method->invoke($scannerPage, $schedules);

        // Expected: Sesi 1 (jam 1-2), Sesi 2 (jam 4)
        $this->assertCount(2, $blocks);
        $this->assertEquals('A', $blocks[0]['class_room']);
        $this->assertEquals([1,2], $blocks[0]['hours']);
        $this->assertEquals('A', $blocks[1]['class_room']);
        $this->assertEquals([4], $blocks[1]['hours']);
    }

    /**
     * Test scan di jam pertama sesi
     */
    public function test_scan_first_hour_of_block()
    {
        // Setup ScheduleTime
        ScheduleTime::updateOrCreate(
            ['hour_number' => 1],
            [
                'start_time' => '08:00',
                'end_time' => '08:35',
                'label' => 'Jam ke 1',
                'is_lesson' => true,
            ]
        );
        ScheduleTime::updateOrCreate(
            ['hour_number' => 2],
            [
                'start_time' => '08:35',
                'end_time' => '09:10',
                'label' => 'Jam ke 2',
                'is_lesson' => true,
            ]
        );

        // Setup data seperti di atas
        $teacher = Teachers::updateOrCreate(
            ['qr_code' => 'TEST123'],
            Teachers::factory()->make(['qr_code' => 'TEST123'])->toArray()
        );
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 1,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 2,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );

        // Mock waktu untuk jam 1 (setelah 08:00)
        Carbon::setTestNow(now()->setDate(2025, 11, 17)->setHour(8)->setMinute(5)); // Monday, jam 1 mulai 08:00

        $scannerPage = new ScannerPage();
        $reflection = new ReflectionClass($scannerPage);
        $property = $reflection->getProperty('selectedClassRoom');
        $property->setAccessible(true);
        $property->setValue($scannerPage, null); // Set to null for no filter
        $method = $reflection->getMethod('processQrCode');
        $method->setAccessible(true);
        $method->invoke($scannerPage, 'TEST123');

        // Check record
        $records = LessonAttendances::where('teacher_id', $teacher->id)
            ->whereDate('date', today())
            ->get();

        $this->assertCount(2, $records);
        $this->assertEquals('present', $records->where('hour_number', 1)->first()->status); // hadir
        $this->assertEquals('present', $records->where('hour_number', 2)->first()->status); // hadir
    }

    /**
     * Test scan di jam kedua sesi
     */
    public function test_scan_second_hour_of_block()
    {
        // Setup ScheduleTime
        ScheduleTime::updateOrCreate(
            ['hour_number' => 1],
            [
                'start_time' => '08:00',
                'end_time' => '08:35',
                'label' => 'Jam ke 1',
                'is_lesson' => true,
            ]
        );
        ScheduleTime::updateOrCreate(
            ['hour_number' => 2],
            [
                'start_time' => '08:35',
                'end_time' => '09:10',
                'label' => 'Jam ke 2',
                'is_lesson' => true,
            ]
        );

        // Setup similar
        $teacher = Teachers::updateOrCreate(
            ['qr_code' => 'TEST456'],
            Teachers::factory()->make(['qr_code' => 'TEST456'])->toArray()
        );
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 1,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );
        WeeklySchedules::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'day_of_week' => 1,
                'hour_number' => 2,
            ],
            [
                'schedule_time_id' => null,
                'class_room' => 'A',
            ]
        );

        // Mock waktu untuk jam 2 (setelah 08:35)
        Carbon::setTestNow(now()->setDate(2025, 11, 17)->setHour(8)->setMinute(40)); // Monday, jam 2 mulai 08:35

        $scannerPage = new ScannerPage();
        $reflection = new ReflectionClass($scannerPage);
        $property = $reflection->getProperty('selectedClassRoom');
        $property->setAccessible(true);
        $property->setValue($scannerPage, null); // Set to null for no filter
        $method = $reflection->getMethod('processQrCode');
        $method->setAccessible(true);
        $method->invoke($scannerPage, 'TEST456');

        // Check record
        $records = LessonAttendances::where('teacher_id', $teacher->id)
            ->whereDate('date', today())
            ->get();

        $this->assertCount(2, $records);
        $this->assertEquals('absent', $records->where('hour_number', 1)->first()->status); // tidak hadir
        $this->assertEquals('present', $records->where('hour_number', 2)->first()->status); // hadir
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset test time
        parent::tearDown();
    }
}
