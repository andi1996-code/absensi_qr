<?php

namespace App\Filament\Pages;

use App\Models\Salaries;
use App\Services\SalaryCalculationService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;

class GenerateSalariesPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Generate Gaji (Otomatis Semua)';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.generate-salaries';

    protected static ?string $title = 'Generate Gaji Guru';

    public ?array $data = [];
    public bool $showConfirmation = false;

    public function mount(): void
    {
        $this->form->fill([
            'year' => now()->year,
            'month' => now()->month,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('⚙️ Konfigurasi Generate Gaji')
                    ->description('Generate gaji otomatis untuk semua guru berdasarkan absensi pelajaran')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('year')
                                    ->label('Tahun')
                                    ->options(array_combine(
                                        range(now()->year - 2, now()->year + 1),
                                        range(now()->year - 2, now()->year + 1)
                                    ))
                                    ->required(),

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
                                    ->required(),
                            ]),

                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('Tekan tombol di bawah untuk mulai generate gaji otomatis'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $year = (int) $data['year'];
        $month = (int) $data['month'];

        try {
            $service = new SalaryCalculationService();

            // Generate gaji untuk semua guru
            $results = $service->calculateAllTeachersSalary($year, $month);

            if (empty($results)) {
                Notification::make()
                    ->warning()
                    ->title('Tidak Ada Data')
                    ->body('Tidak ada guru aktif untuk generate gaji')
                    ->send();
                return;
            }

            Notification::make()
                ->success()
                ->title('✅ Generate Berhasil')
                ->body(count($results) . ' data gaji berhasil dibuat/diperbarui')
                ->send();

            $this->showConfirmation = false;

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('❌ Error')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
