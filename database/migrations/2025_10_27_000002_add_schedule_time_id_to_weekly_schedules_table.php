<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weekly_schedules', function (Blueprint $table) {
            // Add foreign key to schedule_times
            $table->foreignId('schedule_time_id')
                ->nullable()
                ->after('hour_number')
                ->constrained('schedule_times')
                ->onDelete('set null')
                ->comment('Reference to schedule_times table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['schedule_time_id']);
            $table->dropColumn('schedule_time_id');
        });
    }
};
