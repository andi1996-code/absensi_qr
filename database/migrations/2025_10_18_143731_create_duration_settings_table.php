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
        // create_school_settings_table.php
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('lesson_duration_minutes')->default(45); // 35, 40, 45
            $table->timestamps();
        });

        // Seeder: isi default
        // SchoolSetting::updateOrCreate([], ['lesson_duration_minutes' => 45]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duration_settings');
    }
};
