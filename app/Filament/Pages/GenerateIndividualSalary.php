<?php

namespace App\Filament\Pages;

use App\Models\Teachers;
use App\Models\Salaries;
use App\Services\SalaryCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GenerateIndividualSalary extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static string $view = 'filament.pages.generate-individual-salary';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Generate Gaji';

    public function getTitle(): string
    {
        return 'Generate Gaji Guru';
    }

    public ?array $data = [];
    public array $additionalItems = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pilih Guru & Periode')
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->label('Pilih Guru')
                            ->options(Teachers::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $this->updateSalaryCalculation($state, $get, $set);
                            }),

                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $this->updateSalaryCalculation($get('teacher_id'), $get, $set);
                            }),

                        Forms\Components\TextInput::make('year')
                            ->label('Tahun')
                            ->default(now()->year)
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $this->updateSalaryCalculation($get('teacher_id'), $get, $set);
                            }),
                    ])->columns(3),

                Forms\Components\Section::make('Detail Perhitungan Gaji')
                    ->schema([
                        Forms\Components\Placeholder::make('scheduled_hours')
                            ->label('Total Jam Terjadwal')
                            ->content(fn (Forms\Get $get) => $get('total_scheduled_hours') ? $get('total_scheduled_hours') . ' jam' : '-'),

                        Forms\Components\Placeholder::make('attended_hours')
                            ->label('Jam Hadir')
                            ->content(fn (Forms\Get $get) => $get('attended_hours') ? $get('attended_hours') . ' jam' : '-'),

                        Forms\Components\Placeholder::make('absent_hours')
                            ->label('Jam Tidak Hadir')
                            ->content(fn (Forms\Get $get) => $get('absent_hours') ? $get('absent_hours') . ' jam' : '-'),

                        Forms\Components\Placeholder::make('base_salary')
                            ->label('Gaji Pokok (Hadir + Tidak Hadir)')
                            ->content(fn (Forms\Get $get) => $get('base_salary') ? 'Rp ' . number_format($get('base_salary'), 0, ',', '.') : '-'),

                        Forms\Components\Placeholder::make('position_allowance')
                            ->label('Tunjangan Jabatan')
                            ->content(fn (Forms\Get $get) => $get('position_allowance') ? 'Rp ' . number_format($get('position_allowance'), 0, ',', '.') : '-'),

                        Forms\Components\Placeholder::make('subtotal')
                            ->label('Subtotal (Pokok + Tunjangan)')
                            ->content(function (Forms\Get $get) {
                                $base = $get('base_salary') ?? 0;
                                $allowance = $get('position_allowance') ?? 0;
                                $subtotal = $base + $allowance;
                                return $subtotal > 0 ? 'Rp ' . number_format($subtotal, 0, ',', '.') : '-';
                            }),
                    ])->columns(3)->visible(fn (Forms\Get $get) => $get('teacher_id') !== null),

                Forms\Components\Section::make('Tambahan Gaji (Opsional)')
                    ->schema([
                        Forms\Components\Repeater::make('additionalItems')
                            ->label('Detail Tambahan Gaji')
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->label('Keterangan')
                                    ->placeholder('Contoh: Bonus, Lembur, Tunjangan Khusus')
                                    ->required()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Jumlah (Rp)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Baris')
                            ->collapsible()
                            ->collapsed(),
                    ])->columns(1),

                Forms\Components\Section::make('Potongan Gaji (Opsional)')
                    ->schema([
                        Forms\Components\Repeater::make('deductionItems')
                            ->label('Detail Potongan Gaji')
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->label('Jenis Potongan')
                                    ->placeholder('Contoh: BPJS, Pajak, Denda, Pinjaman')
                                    ->required()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Jumlah (Rp)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Potongan')
                            ->collapsible()
                            ->collapsed(),
                    ])->columns(1),

                Forms\Components\Section::make('Ringkasan Total Gaji')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Placeholder::make('summary_display')
                                    ->label('Rincian Gaji')
                                    ->content(function (Forms\Get $get) {
                                        $base = $get('base_salary') ?? 0;
                                        $allowance = $get('position_allowance') ?? 0;
                                        $additional = 0;
                                        $deductions = 0;

                                        $addItems = $get('additionalItems') ?? [];
                                        if (is_array($addItems)) {
                                            foreach ($addItems as $item) {
                                                $additional += (int) ($item['amount'] ?? 0);
                                            }
                                        }

                                        $dedItems = $get('deductionItems') ?? [];
                                        if (is_array($dedItems)) {
                                            foreach ($dedItems as $item) {
                                                $deductions += (int) ($item['amount'] ?? 0);
                                            }
                                        }

                                        $subtotal = $base + $allowance + $additional;
                                        $net = $subtotal - $deductions;

                                        $lines = [];
                                        $lines[] = 'Gaji Pokok + Tunjangan: Rp ' . number_format($base + $allowance, 0, ',', '.');

                                        if ($additional > 0) {
                                            $lines[] = 'Tambahan: + Rp ' . number_format($additional, 0, ',', '.');
                                        }

                                        if ($deductions > 0) {
                                            $lines[] = 'Potongan: - Rp ' . number_format($deductions, 0, ',', '.');
                                        }

                                        $lines[] = '─────────────────────────';
                                        $lines[] = 'NET SALARY: Rp ' . number_format($net, 0, ',', '.');

                                        return $net > 0 ? implode("\n", $lines) : '-';
                                    }),
                            ])
                            ->columnSpanFull(),
                    ])->visible(fn (Forms\Get $get) => $get('teacher_id') !== null),
            ])
            ->statePath('data');
    }

    protected function updateSalaryCalculation($teacherId, Forms\Get $get, Forms\Set $set): void
    {
        if (!$teacherId) {
            $set('total_scheduled_hours', null);
            $set('attended_hours', null);
            $set('absent_hours', null);
            $set('base_salary', null);
            $set('position_allowance', null);
            return;
        }

        $teacher = Teachers::find($teacherId);
        if (!$teacher) return;

        $month = $get('month') ?? now()->month;
        $year = $get('year') ?? now()->year;

        $service = new SalaryCalculationService();
        $calculation = $service->calculateBaseSalary($teacher, $year, $month);

        $set('total_scheduled_hours', $calculation['scheduled_hours']);
        $set('attended_hours', $calculation['attended_hours']);
        $set('absent_hours', $calculation['absent_hours']);
        $set('base_salary', $calculation['base_salary']);
        $set('position_allowance', $calculation['position_allowance']);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (!$data['teacher_id']) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Pilih guru terlebih dahulu!')
                ->send();
            return;
        }

        // Calculate total additional amount from repeater items
        $totalAdditional = 0;
        $additionalNotes = [];

        if (isset($data['additionalItems']) && is_array($data['additionalItems'])) {
            foreach ($data['additionalItems'] as $item) {
                if (isset($item['amount']) && isset($item['description'])) {
                    $totalAdditional += (int) $item['amount'];
                    $additionalNotes[] = $item['description'] . ': Rp ' . number_format($item['amount'], 0, ',', '.');
                }
            }
        }

        $additionalNotesText = !empty($additionalNotes) ? implode(' | ', $additionalNotes) : null;

        // Calculate total deductions from repeater items
        $totalDeductions = 0;
        $deductionNotes = [];

        if (isset($data['deductionItems']) && is_array($data['deductionItems'])) {
            foreach ($data['deductionItems'] as $item) {
                if (isset($item['amount']) && isset($item['description'])) {
                    $totalDeductions += (int) $item['amount'];
                    $deductionNotes[] = $item['description'] . ': Rp ' . number_format($item['amount'], 0, ',', '.');
                }
            }
        }

        $deductionNotesText = !empty($deductionNotes) ? implode(' | ', $deductionNotes) : null;

        // Check if salary already exists for this teacher/month/year
        $existingSalary = Salaries::where('teacher_id', $data['teacher_id'])
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->first();

        if ($existingSalary) {
            // Update existing
            $existingSalary->update([
                'additional_amount' => $totalAdditional,
                'additional_notes' => $additionalNotesText,
                'deductions_amount' => $totalDeductions,
                'deductions_notes' => $deductionNotesText,
            ]);
            $message = 'Gaji guru berhasil diperbarui!';
        } else {
            // Create new
            $teacher = Teachers::find($data['teacher_id']);
            $service = new SalaryCalculationService();
            $calculation = $service->calculateBaseSalary($teacher, $data['year'], $data['month']);

            $totalBaseSalary = $calculation['base_salary'] + $calculation['position_allowance'];

            Salaries::create([
                'teacher_id' => $data['teacher_id'],
                'year' => $data['year'],
                'month' => $data['month'],
                'total_scheduled_hours' => $calculation['scheduled_hours'],
                'attended_hours' => $calculation['attended_hours'],
                'absent_hours' => $calculation['absent_hours'],
                'total_amount' => $totalBaseSalary,
                'additional_amount' => $totalAdditional,
                'additional_notes' => $additionalNotesText,
                'deductions_amount' => $totalDeductions,
                'deductions_notes' => $deductionNotesText,
                'is_paid' => false,
            ]);
            $message = 'Gaji guru berhasil di-generate!';
        }

        Notification::make()
            ->success()
            ->title('Berhasil')
            ->body($message)
            ->send();

        // Reset form
        $this->form->fill();
    }
}
