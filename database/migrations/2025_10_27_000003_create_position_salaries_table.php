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
        Schema::create('position_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('position')->unique()->comment('Nama jabatan (e.g., Kepala Sekolah, Wakil Kepala, Guru Kelas)');
            $table->decimal('salary_adjustment', 12, 2)->default(0)->comment('Tunjangan gaji untuk jabatan ini');
            $table->text('description')->nullable()->comment('Keterangan tentang tunjangan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_salaries');
    }
};
