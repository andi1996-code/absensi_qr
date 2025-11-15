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
        Schema::create('schedule_times', function (Blueprint $table) {
            $table->id();
            $table->integer('hour_number')->unique()->comment('Nomor jam (1-9)');
            $table->time('start_time')->comment('Waktu mulai jam pelajaran');
            $table->time('end_time')->comment('Waktu selesai jam pelajaran');
            $table->string('label')->nullable()->comment('Label jam (e.g., Jam ke 1, Istirahat, Solat Dzuhur)');
            $table->boolean('is_lesson')->default(true)->comment('true=jam pelajaran, false=istirahat/solat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_times');
    }
};
