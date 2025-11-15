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
        // create_salaries_table.php
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->tinyInteger('month'); // 1-12
            $table->integer('total_scheduled_hours');
            $table->integer('attended_hours');
            $table->integer('absent_hours');
            $table->bigInteger('total_amount');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            $table->unique(['teacher_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
