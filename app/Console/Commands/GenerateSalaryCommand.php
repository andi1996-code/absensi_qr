<?php

namespace App\Console\Commands;

use App\Models\Teachers;
use App\Models\Salaries;
use App\Services\SalaryCalculationService;
use Illuminate\Console\Command;

class GenerateSalaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:generate {--month=10} {--year=2025}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate salary for all active teachers for a specific month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month');
        $year = $this->option('year');

        $this->info("=== GENERATE SALARY ===");
        $this->info("Period: {$month}/{$year}\n");

        $teachers = Teachers::where('is_active', true)->get();

        if ($teachers->count() === 0) {
            $this->error('No active teachers found!');
            return;
        }

        $service = new SalaryCalculationService();
        $results = $service->calculateAllTeachersSalary($year, $month);

        $this->info("Salary generated for " . count($results) . " teachers:\n");

        $table = [];
        foreach ($results as $salary) {
            $table[] = [
                $salary->teacher->name,
                $salary->total_scheduled_hours,
                $salary->attended_hours,
                $salary->absent_hours,
                'Rp ' . number_format($salary->total_amount, 0, ',', '.'),
            ];
        }

        $this->table(
            ['Teacher', 'Scheduled', 'Attended', 'Absent', 'Total Amount'],
            $table
        );

        $this->info("\n✅ Salary calculation completed!");
    }
}
