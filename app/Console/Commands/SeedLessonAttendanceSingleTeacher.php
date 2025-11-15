<?php

namespace App\Console\Commands;

use Database\Seeders\LessonAttendancesSingleTeacherSeeder;
use Illuminate\Console\Command;

class SeedLessonAttendanceSingleTeacher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:lesson-attendance-single-teacher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate 1 bulan absensi pelajaran untuk 1 guru (untuk testing penggajian)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎓 Seeding absensi pelajaran 1 bulan untuk 1 guru...');

        $seeder = new LessonAttendancesSingleTeacherSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        return Command::SUCCESS;
    }
}
