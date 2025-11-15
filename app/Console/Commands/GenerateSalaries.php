<?php

namespace App\Console\Commands;

use App\Services\SalaryCalculationService;
use Illuminate\Console\Command;

class GenerateSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:generate {year} {month} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate gaji guru untuk bulan tertentu';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = (int) $this->argument('year');
        $month = (int) $this->argument('month');
        $generateAll = $this->option('all');

        // Validate input
        if ($month < 1 || $month > 12) {
            $this->error('Bulan harus antara 1-12');
            return Command::FAILURE;
        }

        $service = new SalaryCalculationService();

        if ($generateAll) {
            $this->info("Generating gaji untuk semua guru - {$month}/{$year}...");
            $results = $service->calculateAllTeachersSalary($year, $month);

            foreach ($results as $salary) {
                $this->line(
                    "✅ {$salary->teacher->name}: "
                    . "{$salary->attended_hours}/{$salary->total_scheduled_hours} jam → "
                    . $salary->getFormattedAmount()
                );
            }

            $this->info('✅ Selesai! ' . count($results) . ' guru berhasil digenerate');
        } else {
            $this->error('Gunakan --all untuk generate semua guru');
            $this->info('Contoh: php artisan salary:generate 2025 10 --all');
        }

        return Command::SUCCESS;
    }
}
