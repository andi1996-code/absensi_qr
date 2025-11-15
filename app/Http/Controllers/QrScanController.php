<?php

namespace App\Http\Controllers;

use App\Models\LessonAttendances;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QrScanController extends Controller
{
    /**
     * Process QR code scan
     * Endpoint: POST /api/qr-scan
     * Input: { "qr_code": "QR-XXXXXXXXXX" }
     */
    public function scan(Request $request)
    {
        try {
            $qrCode = $request->input('qr_code');

            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code tidak ditemukan',
                ], 400);
            }

            // Find teacher by QR code
            $teacher = Teachers::where('qr_code', $qrCode)->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak ditemukan',
                ], 404);
            }

            // Get today's schedules
            $today = now();
            $dayOfWeek = $today->dayOfWeek;

            $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak memiliki jadwal hari ini',
                    'teacher' => $teacher->name,
                ], 400);
            }

            // Find which hour right now (berdasarkan jam sekarang)
            $currentHour = $this->getCurrentHour($today);

            if (!$currentHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu scan diluar jam pelajaran',
                    'teacher' => $teacher->name,
                ], 400);
            }

            // Check if hour matches any schedule
            $validSchedule = null;
            foreach ($schedules as $schedule) {
                for ($i = 0; $i < $schedule->total_hours; $i++) {
                    if ($schedule->start_hour + $i == $currentHour) {
                        $validSchedule = $schedule;
                        break 2;
                    }
                }
            }

            if (!$validSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam sekarang bukan jam mengajar',
                    'teacher' => $teacher->name,
                    'current_hour' => $currentHour,
                ], 400);
            }

            // Check if already scanned this hour today
            $alreadyScanned = LessonAttendances::where('teacher_id', $teacher->id)
                ->whereDate('date', $today)
                ->where('hour_number', $currentHour)
                ->first();

            if ($alreadyScanned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah scan di jam ini hari ini',
                    'teacher' => $teacher->name,
                    'scanned_at' => $alreadyScanned->scanned_at->format('H:i:s'),
                ], 409);
            }

            // Create attendance record
            $attendance = LessonAttendances::create([
                'teacher_id' => $teacher->id,
                'date' => $today->toDateString(),
                'hour_number' => $currentHour,
                'scanned_at' => $today,
            ]);

            // Get time range untuk display
            $timeRange = $this->getTimeRange($currentHour);

            return response()->json([
                'success' => true,
                'message' => 'Scan berhasil!',
                'data' => [
                    'teacher_name' => $teacher->name,
                    'date' => $today->format('d M Y'),
                    'hour_number' => $currentHour,
                    'time_range' => $timeRange,
                    'scanned_at' => $today->format('H:i:s'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current hour (Jam Ke) berdasarkan waktu sekarang
     * Jam Ke-1: 08:00-08:45
     * Jam Ke-2: 08:45-09:30
     * etc
     */
    private function getCurrentHour(Carbon $now): ?int
    {
        $hour = $now->hour;
        $minute = $now->minute;

        // Convert to minutes dari 08:00
        $minutesFromEight = ($hour - 8) * 60 + $minute;

        if ($minutesFromEight < 0 || $minutesFromEight >= 8 * 45) {
            return null; // Outside school hours
        }

        // Calculate which hour
        $hourNumber = intdiv($minutesFromEight, 45) + 1;

        return $hourNumber <= 8 ? $hourNumber : null;
    }

    /**
     * Get time range dari jam ke
     */
    private function getTimeRange(int $hour): string
    {
        $startMinutes = ($hour - 1) * 45;
        $startHour = 8 + intdiv($startMinutes, 60);
        $startMinute = $startMinutes % 60;

        $endMinutes = $startMinutes + 45;
        $endHour = 8 + intdiv($endMinutes, 60);
        $endMinute = $endMinutes % 60;

        return sprintf('%02d:%02d - %02d:%02d', $startHour, $startMinute, $endHour, $endMinute);
    }

    /**
     * Get stats untuk display di monitor
     * Endpoint: GET /api/qr-scan/stats
     */
    public function getStats()
    {
        $today = now();
        $dayOfWeek = $today->dayOfWeek;

        // Get all active teachers
        $teachers = Teachers::where('is_active', true)->get();

        $stats = [];
        foreach ($teachers as $teacher) {
            $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->get();

            if ($schedules->isNotEmpty()) {
                $totalHours = $schedules->sum('total_hours');
                $scannedHours = LessonAttendances::where('teacher_id', $teacher->id)
                    ->whereDate('date', $today)
                    ->count();

                $stats[] = [
                    'teacher_name' => $teacher->name,
                    'total_hours' => $totalHours,
                    'scanned_hours' => $scannedHours,
                    'status' => $scannedHours == 0 ? 'Belum Scan' : ($scannedHours == $totalHours ? 'Selesai' : 'Scan Sebagian'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'date' => $today->format('d M Y'),
            'day' => $today->format('l'),
            'stats' => $stats,
        ]);
    }
}
