<?php

namespace App\Filament\Pages;

use App\Models\DuhaAttendances;
use App\Models\DepartureAttendances;
use App\Models\LessonAttendances;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Filament\Tables\Table;

class AttendanceRecap extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Rekap Absen';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.attendance-recap';

    public ?array $data = [];

    public ?int $teacher_id = null;

    public ?string $filter_type = 'daily'; // 'daily', 'weekly', 'monthly'

    public ?string $selected_date = null;

    public ?string $attendance_type = 'all'; // 'all', 'duha', 'departure', 'lesson'

    public array $attendance_data = [];

    protected function getListeners(): array
    {
        return [
            'filterUpdated' => 'refreshData',
        ];
    }

    public function refreshData(): void
    {
        // Extract data from form
        $formData = $this->form->getState();

        // Convert empty string to null for teacher_id
        $teacherId = $formData['teacher_id'] ?? null;
        $this->teacher_id = ($teacherId === '' || $teacherId === null) ? null : (int)$teacherId;

        $this->filter_type = $formData['filter_type'] ?? 'daily';
        $this->attendance_type = $formData['attendance_type'] ?? 'all';

        // Set selected_date otomatis ke hari ini (tanpa input dari user)
        $this->selected_date = now()->toDateString();

        // Refresh attendance data
        $this->attendance_data = $this->getAttendanceDetails();
    }

    public function updated($name, $value): void
    {
        // Legacy support - trigger refresh when properties change directly
        if (in_array($name, ['teacher_id', 'filter_type', 'selected_date', 'attendance_type'])) {
            $this->attendance_data = $this->getAttendanceDetails();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => $this->exportToExcel())
                ->visible(fn() => $this->selected_date !== null),
        ];
    }

    public function mount(): void
    {
        $this->selected_date = now()->toDateString();
        $this->filter_type = 'daily';
        $this->attendance_type = 'all';

        // Fill form with initial data
        $this->form->fill([
            'teacher_id' => $this->teacher_id,
            'filter_type' => $this->filter_type,
            'selected_date' => $this->selected_date,
            'attendance_type' => $this->attendance_type,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter Absen')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->label('Pilih Guru')
                            ->options(function () {
                                $teachers = Teachers::where('is_active', true)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn($teacher) => [$teacher->id => $teacher->name])
                                    ->toArray();

                                return [null => 'Semua Guru'] + $teachers;
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn() => $this->dispatch('filterUpdated')),

                        Forms\Components\Select::make('filter_type')
                            ->label('Jenis Filter')
                            ->options([
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                            ])
                            ->default('daily')
                            ->live()
                            ->afterStateUpdated(fn() => $this->dispatch('filterUpdated')),

                        Forms\Components\Select::make('attendance_type')
                            ->label('Tipe Absen')
                            ->options([
                                'all' => 'Semua Absen',
                                'duha' => 'Absen Duha (Masuk)',
                                'departure' => 'Absen Pulang (Keluar)',
                                'lesson' => 'Absen Pelajaran',
                            ])
                            ->default('all')
                            ->live()
                            ->afterStateUpdated(fn() => $this->dispatch('filterUpdated')),
                    ]),
            ])
            ->statePath('data');
    }

    public function getInfolists(): array
    {
        return [];
    }

    protected function summaryInfolist(): Infolists\Infolist
    {
        return Infolists\Infolist::make()
            ->state($this->getSummaryData())
            ->schema([
                Infolists\Components\Section::make('Ringkasan Absen')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('total_scheduled')
                            ->label('Total Jam Mengajar')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('total_attended')
                            ->label('Jam Hadir')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('total_absent')
                            ->label('Jam Absen')
                            ->badge()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('attendance_percentage')
                            ->label('Persentase Kehadiran')
                            ->badge()
                            ->color('warning'),
                    ]),
            ]);
    }

    public function getSummaryData(): array
    {
        if (!$this->selected_date) {
            return [
                'duha_count' => 0,
                'duha_late' => 0,
                'departure_count' => 0,
                'departure_early' => 0,
                'lesson_count' => 0,
                'attendance_percentage' => '0%',
            ];
        }

        $date = Carbon::parse($this->selected_date);

        if ($this->filter_type === 'daily') {
            $startDate = $date->copy();
            $endDate = $date->copy();
        } elseif ($this->filter_type === 'weekly') {
            $startDate = $date->copy()->startOfWeek();
            $endDate = $date->copy()->endOfWeek();
        } else {
            // Monthly
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
        }

        $teacherQuery = $this->teacher_id ? Teachers::where('id', $this->teacher_id) : Teachers::where('is_active', true);

        $totalDuhaCount = 0;
        $totalDuhaLate = 0;
        $totalDepartureCount = 0;
        $totalDepartureEarly = 0;
        $totalLessonCount = 0;
        $totalScheduledDays = 0;
        $totalPresentDays = 0;

        foreach ($teacherQuery->get() as $teacher) {
            // Count Duha Attendances
            $duhaAttendances = DuhaAttendances::where('teacher_id', $teacher->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $totalDuhaCount += $duhaAttendances->count();
            $totalDuhaLate += $duhaAttendances->where('is_late', '>', 0)->count();

            // Count Departure Attendances
            $departureAttendances = DepartureAttendances::where('teacher_id', $teacher->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $totalDepartureCount += $departureAttendances->count();
            $totalDepartureEarly += $departureAttendances->where('is_late', '>', 0)->count();

            // Count Lesson Attendances
            $totalLessonCount += LessonAttendances::where('teacher_id', $teacher->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->count();

            // Get all schedules in range for percentage calculation
            $schedules = WeeklySchedules::where('teacher_id', $teacher->id)->get();
            $teacherPresentDays = 0;
            $teacherScheduledDays = 0;

            for ($d = $startDate->copy(); $d <= $endDate; $d->addDay()) {
                // Skip Sundays
                if ($d->dayOfWeek === 0) {
                    continue;
                }

                // Convert Carbon dayOfWeek (0=Sunday, 1=Monday... 6=Saturday) to our format (1=Monday...6=Saturday, 7=Sunday)
                $dayOfWeek = $d->dayOfWeek === 0 ? 7 : $d->dayOfWeek;

                // Check if teacher has schedule on this day
                $hasSchedule = $schedules->contains('day_of_week', $dayOfWeek);
                if ($hasSchedule) {
                    $teacherScheduledDays++;
                    // Check if teacher was present on this day (has duha attendance)
                    $hasDuhaAttendance = DuhaAttendances::where('teacher_id', $teacher->id)
                        ->whereDate('date', $d->toDateString())
                        ->exists();
                    if ($hasDuhaAttendance) {
                        $teacherPresentDays++;
                    }
                }
            }

            $totalScheduledDays += $teacherScheduledDays;
            $totalPresentDays += $teacherPresentDays;
        }

        $attendancePercentage = $totalScheduledDays > 0
            ? round(($totalPresentDays / $totalScheduledDays) * 100, 2)
            : 0;

        return [
            'duha_count' => $totalDuhaCount,
            'duha_late' => $totalDuhaLate,
            'departure_count' => $totalDepartureCount,
            'departure_early' => $totalDepartureEarly,
            'lesson_count' => $totalLessonCount,
            'attendance_percentage' => $attendancePercentage . '%',
        ];
    }

    public function getAttendanceDetails(): array
    {
        if (!$this->selected_date) {
            return [];
        }

        $date = Carbon::parse($this->selected_date);

        if ($this->filter_type === 'daily') {
            $startDate = $date->copy();
            $endDate = $date->copy();
        } elseif ($this->filter_type === 'weekly') {
            $startDate = $date->copy()->startOfWeek();
            $endDate = $date->copy()->endOfWeek();
        } else {
            // Monthly
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
        }

        $details = [];
        $teacherQuery = $this->teacher_id ? Teachers::where('id', $this->teacher_id) : Teachers::where('is_active', true);

        foreach ($teacherQuery->get() as $teacher) {
            // Get Duha Attendances
            if (in_array($this->attendance_type, ['all', 'duha'])) {
                $duhaRecords = DuhaAttendances::where('teacher_id', $teacher->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orderBy('date', 'desc')
                    ->get();

                foreach ($duhaRecords as $record) {
                    $details[] = [
                        'teacher_name' => $teacher->name,
                        'teacher_nip' => $teacher->nip,
                        'type' => 'DUHA',
                        'date' => Carbon::parse($record->date)->format('d M Y'),
                        'day' => Carbon::parse($record->date)->format('l'),
                        'time' => Carbon::parse($record->scanned_at)->format('H:i:s'),
                        'status' => $record->is_late > 0 ? "TERLAMBAT {$record->is_late} MENIT" : 'TEPAT WAKTU',
                        'late_minutes' => $record->is_late,
                        'badge_color' => $record->is_late > 0 ? 'danger' : 'success',
                    ];
                }
            }

            // Get Departure Attendances
            if (in_array($this->attendance_type, ['all', 'departure'])) {
                $departureRecords = DepartureAttendances::where('teacher_id', $teacher->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orderBy('date', 'desc')
                    ->get();

                foreach ($departureRecords as $record) {
                    $details[] = [
                        'teacher_name' => $teacher->name,
                        'teacher_nip' => $teacher->nip,
                        'type' => 'PULANG',
                        'date' => Carbon::parse($record->date)->format('d M Y'),
                        'day' => Carbon::parse($record->date)->format('l'),
                        'time' => Carbon::parse($record->scanned_at)->format('H:i:s'),
                        'status' => $record->is_late > 0 ? "PULANG AWAL {$record->is_late} MENIT" : 'NORMAL',
                        'late_minutes' => $record->is_late,
                        'badge_color' => $record->is_late > 0 ? 'warning' : 'success',
                    ];
                }
            }

            // Get Lesson Attendances
            if (in_array($this->attendance_type, ['all', 'lesson'])) {
                $lessonRecords = LessonAttendances::where('teacher_id', $teacher->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orderBy('date', 'desc')
                    ->get();

                foreach ($lessonRecords as $record) {
                    $details[] = [
                        'teacher_name' => $teacher->name,
                        'teacher_nip' => $teacher->nip,
                        'type' => 'PELAJARAN',
                        'date' => Carbon::parse($record->date)->format('d M Y'),
                        'day' => Carbon::parse($record->date)->format('l'),
                        'time' => Carbon::parse($record->scanned_at)->format('H:i:s'),
                        'status' => "JAM KE-{$record->hour_number}",
                        'late_minutes' => 0,
                        'badge_color' => 'info',
                    ];
                }
            }
        }

        // Sort by date descending
        usort($details, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $details;
    }

    public function exportToExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new AttendanceExport(
                $this->getAttendanceDetails(),
                $this->getSummaryData(),
                $this->filter_type,
                $this->selected_date,
                $this->attendance_type,
                $this->teacher_id
            ),
            'rekap_absensi_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }
}

class AttendanceExport implements FromArray, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $summary;
    protected $filterType;
    protected $selectedDate;
    protected $attendanceType;
    protected $teacherId;

    public function __construct($data, $summary, $filterType, $selectedDate, $attendanceType, $teacherId)
    {
        $this->data = $data;
        $this->summary = $summary;
        $this->filterType = $filterType;
        $this->selectedDate = $selectedDate;
        $this->attendanceType = $attendanceType;
        $this->teacherId = $teacherId;
    }

    public function array(): array
    {
        $rows = [];

        // === HEADER UTAMA ===
        $rows[] = ['LAPORAN REKAP ABSENSI GURU'];
        $rows[] = [''];
        $rows[] = ['Periode', $this->filterType === 'daily' ? 'Harian' : ($this->filterType === 'weekly' ? 'Mingguan' : 'Bulanan')];
        $rows[] = ['Tanggal Referensi', Carbon::parse($this->selectedDate)->format('d M Y')];
        $rows[] = ['Tipe Absen', $this->attendanceType === 'all' ? 'Semua Absen' : ($this->attendanceType === 'duha' ? 'Absen Duha' : ($this->attendanceType === 'departure' ? 'Absen Pulang' : 'Absen Pelajaran'))];
        $rows[] = ['Guru', $this->teacherId ? \App\Models\Teachers::find($this->teacherId)?->name : 'Semua Guru'];
        $rows[] = ['Tanggal Export', now()->format('d M Y H:i:s')];
        $rows[] = [''];
        $rows[] = [''];

        // === TABEL DATA ===
        $rows[] = [
            'No',
            'Nama Guru',
            'NIP',
            'Tipe Absen',
            'Tanggal',
            'Hari',
            'Waktu Scan',
            'Status',
            'Keterlambatan (Menit)'
        ];

        $no = 1;
        foreach ($this->data as $row) {
            $nip = isset($row['teacher_nip']) ? "'" . $row['teacher_nip'] : '';

            $rows[] = [
                $no++,
                $row['teacher_name'] ?? '',
                $nip,
                $row['type'],
                $row['date'],
                $row['day'],
                $row['time'],
                $row['status'],
                $row['late_minutes']
            ];
        }


        $rows[] = [''];
        $rows[] = [''];
        $rows[] = ['RINGKASAN ABSENSI'];
        $rows[] = ['Total Absen Duha', $this->summary['duha_count']];
        $rows[] = ['Total Terlambat Duha', $this->summary['duha_late']];
        $rows[] = ['Total Absen Pulang', $this->summary['departure_count']];
        $rows[] = ['Pulang Awal', $this->summary['departure_early']];
        $rows[] = ['Total Absen Pelajaran', $this->summary['lesson_count']];
        $rows[] = ['Persentase Kehadiran', $this->summary['attendance_percentage']];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // === Judul utama ===
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // === Header tabel ===
        $headerRow = 10; // karena header tabel dimulai setelah beberapa baris informasi
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // === Border untuk semua data ===
        $lastRow = 9 + count($this->data) + 1;
        $sheet->getStyle("A{$headerRow}:I{$lastRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // === Lebar kolom ===
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(25); // Kolom NIP lebih lebar
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(20);

        // === Freeze header ===
        $sheet->freezePane('A11');

        // === Wrap text dan alignment tengah untuk data ===
        $sheet->getStyle("A{$headerRow}:I{$lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // === Format kolom NIP sebagai teks ===
        $sheet->getStyle("C11:C{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('@');

        // === Bagian Ringkasan ===
        $summaryStart = $lastRow + 3;
        $sheet->getStyle("A{$summaryStart}:B" . ($summaryStart + 6))
            ->getFont()->setBold(true);
        $sheet->getStyle("A{$summaryStart}:B" . ($summaryStart + 6))
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E3F2FD');

        return $sheet;
    }
}
