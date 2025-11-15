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
        Schema::table('teachers', function (Blueprint $table) {
            // Cek apakah kolom sudah ada (jika belum ada di create_teachers_table)
            if (!Schema::hasColumn('teachers', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('phone')->comment('Path to teacher photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
