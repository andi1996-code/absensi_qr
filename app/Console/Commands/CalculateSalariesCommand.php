<?php

namespace App\Console\Commands;

use App\Services\SalaryCalculationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CalculateSalariesCommand extends Command
{
    protected $signature = 'salary:calculate {--year= : Year (default: current year)} {--month= : Month (default: current month)} {--rate=100000 : Hourly rate}';
    protected $description = 'Calculate salaries for all teachers for a specific month';

    public function __construct(
        private SalaryCalculationService $salaryService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $year = $this->option('year') ?? date('Y');
        $month = $this->option('month') ?? date('m');
        $rate = $this->option('rate') ?? 100000;

        $this->info("Calculating salaries for {$month}/{$year} with hourly rate Rp " . number_format($rate, 0, ',', '.'));

        try {
            $results = $this->salaryService->calculateAllTeachersSalary($year, $month, $rate);

            $this->info("\n✓ Salary calculation completed for " . count($results) . " teachers\n");

            // Display summary
            $this->table(
                ['Teacher', 'Scheduled Hours', 'Attended Hours', 'Absent Hours', 'Total Amount'],
                collect($results)->map(function ($salary) {
                    return [
                        $salary->teacher->name,
                        $salary->total_scheduled_hours,
                        $salary->attended_hours,
                        $salary->absent_hours,
                        'Rp ' . number_format($salary->total_amount, 0, ',', '.'),
                    ];
                })->toArray()
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
