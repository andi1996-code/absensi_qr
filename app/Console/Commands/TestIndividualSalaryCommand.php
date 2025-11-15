<?php

namespace App\Console\Commands;

use App\Models\Teachers;
use App\Models\Salaries;
use App\Services\SalaryCalculationService;
use Illuminate\Console\Command;

class TestIndividualSalaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:salary {teacher_id} {--month=10} {--year=2025} {--additional=0} {--notes=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test individual salary generation with additional amount';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teacherId = $this->argument('teacher_id');
        $month = $this->option('month');
        $year = $this->option('year');
        $additional = $this->option('additional');
        $notes = $this->option('notes');

        $teacher = Teachers::find($teacherId);
        if (!$teacher) {
            $this->error("Teacher with ID {$teacherId} not found!");
            return;
        }

        $this->info("=== TEST INDIVIDUAL SALARY GENERATION ===");
        $this->info("Teacher: {$teacher->name} ({$teacher->position})");
        $this->info("Period: {$month}/{$year}");
        $this->info("Additional Amount: Rp " . number_format($additional, 0, ',', '.'));
        if ($notes) {
            $this->info("Notes: {$notes}");
        }
        $this->info("");

        $service = new SalaryCalculationService();
        $calculation = $service->calculateBaseSalary($teacher, $year, $month);

        $this->info("=== CALCULATION DETAILS ===");
        $this->table(
            ['Item', 'Value'],
            [
                ['Total Scheduled Hours', $calculation['scheduled_hours'] . ' jam'],
                ['Attended Hours', $calculation['attended_hours'] . ' jam'],
                ['Absent Hours', $calculation['absent_hours'] . ' jam'],
                ['Base Salary (Hadir + Tidak Hadir)', 'Rp ' . number_format($calculation['base_salary'], 0, ',', '.')],
                ['Position Allowance', 'Rp ' . number_format($calculation['position_allowance'], 0, ',', '.')],
                ['Subtotal (Base + Allowance)', 'Rp ' . number_format($calculation['base_salary'] + $calculation['position_allowance'], 0, ',', '.')],
                ['Additional Amount', 'Rp ' . number_format($additional, 0, ',', '.')],
                ['GRAND TOTAL', 'Rp ' . number_format($calculation['base_salary'] + $calculation['position_allowance'] + $additional, 0, ',', '.')],
            ]
        );

        // Create salary record
        $salary = Salaries::create([
            'teacher_id' => $teacherId,
            'year' => $year,
            'month' => $month,
            'total_scheduled_hours' => $calculation['scheduled_hours'],
            'attended_hours' => $calculation['attended_hours'],
            'absent_hours' => $calculation['absent_hours'],
            'total_amount' => $calculation['base_salary'] + $calculation['position_allowance'],
            'additional_amount' => $additional,
            'additional_notes' => $notes,
            'is_paid' => false,
        ]);

        $this->info("");
        $this->info("✅ Salary record created successfully!");
        $this->info("Salary ID: {$salary->id}");

        return 0;
    }
}
