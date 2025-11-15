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
        Schema::table('duha_attendances', function (Blueprint $table) {
            // Ubah kolom is_late dari boolean menjadi integer (menit terlambat)
            $table->integer('is_late')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duha_attendances', function (Blueprint $table) {
            // Kembalikan ke boolean
            $table->boolean('is_late')->default(false)->change();
        });
    }
};
