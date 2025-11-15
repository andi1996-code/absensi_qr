<?php

namespace App\Console\Commands;

use App\Models\PositionSalary;
use App\Models\Teachers;
use App\Services\SalaryCalculationService;
use Illuminate\Console\Command;

class VerifySalaryAllowance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:salary-allowance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify position salary allowances and teacher allocations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Verifikasi Tunjangan Gaji Jabatan ===');
        $this->newLine();

        // Show position salaries
        $this->info('📊 Data Tunjangan Jabatan:');
        $salaries = PositionSalary::all();
        if ($salaries->isEmpty()) {
            $this->warn('Tidak ada data tunjangan gaji');
            return;
        }

        $headers = ['Jabatan', 'Tunjangan', 'Keterangan', 'Status'];
        $rows = $salaries->map(fn($s) => [
            $s->position,
            'Rp ' . number_format($s->salary_adjustment, 0, ',', '.'),
            $s->description ?? '-',
            $s->is_active ? '✓ Aktif' : '✗ Nonaktif',
        ])->toArray();

        $this->table($headers, $rows);
        $this->newLine();

        // Show teacher salary allocations
        $this->info('👨‍🏫 Alokasi Gaji Guru:');
        $teachers = Teachers::where('is_active', true)->get();

        $salaryCalcService = app(SalaryCalculationService::class);
        $teacherRows = $teachers->map(function($teacher) use ($salaryCalcService) {
            $allowance = $salaryCalcService->getPositionAllowance($teacher);
            return [
                $teacher->name,
                $teacher->position ?? '-',
                'Rp ' . number_format($allowance, 0, ',', '.'),
            ];
        })->toArray();

        $this->table(['Nama Guru', 'Jabatan', 'Tunjangan'], $teacherRows);
        $this->newLine();

        // Summary
        $this->info('📈 Ringkasan:');
        $this->line('• Total Jabatan Aktif: ' . PositionSalary::active()->count());
        $this->line('• Total Guru Aktif: ' . Teachers::where('is_active', true)->count());
        $this->line('• Guru dengan Jabatan: ' . Teachers::where('is_active', true)->whereNotNull('position')->count());

        $this->newLine();
        $this->info('✅ Verifikasi selesai!');
    }
}
