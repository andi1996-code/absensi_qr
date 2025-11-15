<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teachers;

class VerifyPositions extends Command
{
    protected $signature = 'verify:positions';
    protected $description = 'Verify teacher positions';

    public function handle()
    {
        $teachers = Teachers::all();

        $this->info('Teacher Positions:');
        $this->newLine();

        foreach ($teachers as $teacher) {
            $this->line("{$teacher->id}. {$teacher->name} - {$teacher->position}");
        }

        return 0;
    }
}
