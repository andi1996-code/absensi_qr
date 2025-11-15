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
        // create_lesson_attendances_table.php
        Schema::create('lesson_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->tinyInteger('hour_number'); // 1-8
            $table->timestamp('scanned_at');
            $table->unique(['teacher_id', 'date', 'hour_number']); // hanya 1x per sesi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_attendances');
    }
};
