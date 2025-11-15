<?php

namespace App\Console\Commands;

use App\Exports\TeachersTemplateExport;
use App\Imports\TeachersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Console\Command;

class TestTeacherImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:teacher-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test teacher import functionality by creating template and importing sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Teacher Import Functionality');
        $this->newLine();

        // Step 1: Create template
        $this->info('Step 1: Creating template file...');
        $templatePath = storage_path('app/public/test-template-guru.xlsx');
        Excel::store(new TeachersTemplateExport(), 'public/test-template-guru.xlsx');
        $this->line('✓ Template created at: ' . $templatePath);
        $this->newLine();

        // Step 2: Import sample data
        $this->info('Step 2: Importing sample data from template...');
        try {
            $import = new TeachersImport();
            Excel::import($import, $templatePath);
            $this->line('✓ Import successful');
        } catch (\Exception $e) {
            $this->error('✗ Import failed: ' . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // Step 3: Verify imported data
        $this->info('Step 3: Verifying imported data...');
        $teachers = \App\Models\Teachers::where('name', 'like', '%Budi%')
            ->orWhere('name', 'like', '%Siti%')
            ->orWhere('name', 'like', '%Ahmad%')
            ->get();

        if ($teachers->isEmpty()) {
            $this->warn('No imported teachers found');
            return;
        }

        $headers = ['ID', 'Nama', 'NIP', 'QR Code', 'Email', 'Jabatan'];
        $rows = $teachers->map(fn($t) => [
            $t->id,
            $t->name,
            $t->nip,
            $t->qr_code,
            $t->email,
            $t->position,
        ])->toArray();

        $this->table($headers, $rows);
        $this->newLine();

        $this->info('✅ Teacher import test completed successfully!');
        $this->line('Template file: ' . $templatePath);
    }
}
