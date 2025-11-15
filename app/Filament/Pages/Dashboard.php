<?php

namespace App\Filament\Pages;

use App\Models\Teachers;
use App\Models\Salaries;
use App\Models\LessonAttendances;
use App\Models\SchoolProfile;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getStats(): array
    {
        return [];
    }

    protected function getAttendanceRate(): float
    {
        // Hitung attendance rate berdasarkan guru aktif vs scheduled hours yang tercatat
        $activeTeachers = Teachers::where('is_active', true)->count();
        if ($activeTeachers === 0) {
            return 0;
        }

        // Total unique teachers yang punya record kehadiran
        $teachersWithAttendance = LessonAttendances::distinct('teacher_id')->count('teacher_id');
        return ($teachersWithAttendance / $activeTeachers) * 100;
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }

    public function getDashboardData(): array
    {
        $activeTeachers = Teachers::where('is_active', true)->count();
        $totalTeachers = Teachers::count();
        $totalSalaries = Salaries::sum('total_amount') ?? 0;
        $attendanceRate = $this->getAttendanceRate();
        $schoolProfile = SchoolProfile::first();
        $monthSalaries = Salaries::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount') ?? 0;

        return [
            'activeTeachers' => $activeTeachers,
            'totalTeachers' => $totalTeachers,
            'totalSalaries' => $totalSalaries,
            'monthSalaries' => $monthSalaries,
            'attendanceRate' => round($attendanceRate, 1),
            'schoolProfile' => $schoolProfile,
            'activeTeachersPercentage' => $totalTeachers > 0 ? round(($activeTeachers / $totalTeachers) * 100, 1) : 0,
        ];
    }
}
