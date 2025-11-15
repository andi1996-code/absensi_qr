<?php

namespace App\Services;

use App\Models\Teachers;
use App\Models\PositionSalary;
use App\Models\WeeklySchedules;
use App\Models\LessonAttendances;
use App\Models\Salaries;
use Carbon\Carbon;

class SalaryCalculationService
{
    /**
     * Calculate salary for a teacher for a specific month/year
     *
     * Aturan:
     * - Hadir: Rp 7.500/jam
     * - Tidak Hadir (Honor): Rp 3.500/jam
     * - Tunjangan Jabatan: Tambahan sesuai posisi
     */
    public function calculateSalary(
        Teachers $teacher,
        int $year,
        int $month,
        int $hourlyRateAttended = 7500,
        int $hourlyRateAbsent = 3500
    ): Salaries {
        // Get all scheduled hours for this month
        $scheduledHours = $this->getTotalScheduledHours($teacher, $year, $month);

        // Get all attended hours for this month
        $attendedHours = $this->getTotalAttendedHours($teacher, $year, $month);

        // Calculate absent hours
        $absentHours = $scheduledHours - $attendedHours;

        // Calculate total amount
        // Hadir: attended_hours * 7500
        // Tidak hadir: absent_hours * 3500
        // + Tunjangan jabatan
        $totalAmount = ($attendedHours * $hourlyRateAttended) + ($absentHours * $hourlyRateAbsent);

        // Add position allowance
        $positionAllowance = $this->getPositionAllowance($teacher);
        $totalAmount += $positionAllowance;

        // Create or update salary record
        $salary = Salaries::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'total_scheduled_hours' => $scheduledHours,
                'attended_hours' => $attendedHours,
                'absent_hours' => $absentHours,
                'total_amount' => $totalAmount,
                'is_paid' => false,
            ]
        );

        return $salary;
    }

    /**
     * Calculate base salary for a teacher (without position allowance)
     * Used for individual salary generation form
     */
    public function calculateBaseSalary(
        Teachers $teacher,
        int $year,
        int $month,
        int $hourlyRateAttended = 7500,
        int $hourlyRateAbsent = 3500
    ): array {
        // Get all scheduled hours for this month
        $scheduledHours = $this->getTotalScheduledHours($teacher, $year, $month);

        // Get all attended hours for this month
        $attendedHours = $this->getTotalAttendedHours($teacher, $year, $month);

        // Calculate absent hours
        $absentHours = $scheduledHours - $attendedHours;

        // Calculate base salary (without position allowance)
        $baseSalary = ($attendedHours * $hourlyRateAttended) + ($absentHours * $hourlyRateAbsent);

        return [
            'scheduled_hours' => $scheduledHours,
            'attended_hours' => $attendedHours,
            'absent_hours' => $absentHours,
            'base_salary' => $baseSalary,
            'position_allowance' => $this->getPositionAllowance($teacher),
        ];
    }
    public function getPositionAllowance(Teachers $teacher): int
    {
        if (!$teacher->position) {
            return 0;
        }

        $positionSalary = PositionSalary::where('position', $teacher->position)
            ->where('is_active', true)
            ->first();

        return $positionSalary ? (int) $positionSalary->salary_adjustment : 0;
    }

    /**
     * Get total scheduled hours for a teacher in a specific month
     * Jadwal mingguan dihitung berdasarkan jumlah minggu dalam bulan
     */
    public function getTotalScheduledHours(Teachers $teacher, int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get all weekly schedules for this teacher
        $weeklySchedules = WeeklySchedules::where('teacher_id', $teacher->id)
            ->get();

        $totalHours = 0;

        // Count how many times each schedule day occurs in this month
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dayOfWeek = $current->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
            // Convert to our format: 1=Monday, 2=Tuesday, ..., 6=Saturday, 7=Sunday
            $dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;

            // Check if teacher has schedule on this day
            $schedulesForDay = $weeklySchedules->where('day_of_week', $dayOfWeek);
            $totalHours += $schedulesForDay->count();

            $current->addDay();
        }

        return $totalHours;
    }

    /**
     * Get total attended hours for a teacher in a specific month
     * Dihitung dari LessonAttendances yang ada
     */
    public function getTotalAttendedHours(Teachers $teacher, int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $attendedHours = LessonAttendances::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        return $attendedHours;
    }

    /**
     * Get attendance details for a teacher in a specific month
     * Includes scheduled vs attended comparison
     */
    public function getAttendanceDetails(Teachers $teacher, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $details = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayOfWeek = $current->dayOfWeek;
            $dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;

            // Get scheduled hours for this day
            $scheduledHours = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->get();

            // Get attended hours for this date
            $attendances = LessonAttendances::where('teacher_id', $teacher->id)
                ->where('date', $current->toDateString())
                ->get();

            foreach ($scheduledHours as $schedule) {
                $attended = $attendances->where('hour_number', $schedule->hour_number)->first() !== null;

                $details[] = [
                    'date' => $current->toDateString(),
                    'day_name' => $this->getDayName($dayOfWeek),
                    'hour_number' => $schedule->hour_number,
                    'start_time' => $this->getStartTime($schedule->hour_number),
                    'end_time' => $this->getEndTime($schedule->hour_number),
                    'attended' => $attended,
                ];
            }

            $current->addDay();
        }

        return $details;
    }

    /**
     * Get start time for hour number (jam ke-1 mulai pukul 08:00)
     * Assuming each session is 45 minutes
     */
    public function getStartTime(int $hourNumber): string
    {
        $baseHour = 8; // Jam ke-1 dimulai jam 08:00
        $durationMinutes = 45; // Durasi per jam (bisa dari DurationSetting)

        $totalMinutes = ($hourNumber - 1) * $durationMinutes;
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $baseHour + $hours, $minutes);
    }

    /**
     * Get end time for hour number
     */
    public function getEndTime(int $hourNumber): string
    {
        $baseHour = 8;
        $durationMinutes = 45;

        $totalMinutes = $hourNumber * $durationMinutes;
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $baseHour + $hours, $minutes);
    }

    /**
     * Get day name in Indonesian
     */
    private function getDayName(int $dayOfWeek): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $days[$dayOfWeek] ?? 'Unknown';
    }

    /**
     * Calculate salary for all teachers for a specific month
     */
    public function calculateAllTeachersSalary(
        int $year,
        int $month,
        int $hourlyRateAttended = 7500,
        int $hourlyRateAbsent = 3500
    ): array {
        $teachers = Teachers::where('is_active', true)->get();
        $results = [];

        foreach ($teachers as $teacher) {
            $results[] = $this->calculateSalary(
                $teacher,
                $year,
                $month,
                $hourlyRateAttended,
                $hourlyRateAbsent
            );
        }

        return $results;
    }
}
