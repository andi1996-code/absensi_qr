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
        // create_school_profile_table.php
        Schema::create('school_profile', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Nama sekolah
            $table->string('npsn')->nullable();     // NPSN
            $table->string('address')->nullable();  // Alamat lengkap
            $table->string('phone')->nullable();    // Telepon
            $table->string('email')->nullable();    // Email resmi
            $table->string('logo_path')->nullable(); // Path logo (untuk kop & PDF)
            $table->text('header_text')->nullable(); // Kalimat kepala (opsional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
    }
};
