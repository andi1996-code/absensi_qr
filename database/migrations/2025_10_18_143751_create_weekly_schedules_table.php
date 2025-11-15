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
        // create_weekly_schedules_table.php
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('1=Senin, 2=Selasa, ..., 6=Sabtu'); // 1-6
            $table->tinyInteger('hour_number')->comment('1-8'); // Jam Ke-1 s.d. Ke-8
            $table->unique(['teacher_id', 'day_of_week', 'hour_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};
