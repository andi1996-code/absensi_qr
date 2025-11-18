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
        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->foreignId('class_room_id')
                ->nullable()
                ->after('hour_number')
                ->constrained('class_rooms')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['class_room_id']);
            $table->dropColumn('class_room_id');
        });
    }
};
