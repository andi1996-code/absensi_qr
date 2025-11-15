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
            // Ubah NIP menjadi nullable dan hapus unique constraint
            $table->string('nip')->nullable()->change();

            // Tambah kolom QR code
            $table->string('qr_code')->unique()->after('nip')->comment('Unique QR code untuk scanning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('qr_code');
            $table->string('nip')->unique()->change();
        });
    }
};
