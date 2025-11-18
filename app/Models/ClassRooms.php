<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRooms extends Model
{
    use HasFactory;

    protected $table = 'class_rooms';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Weekly schedules that belong to this class
     */
    public function weeklySchedules(): HasMany
    {
        return $this->hasMany(WeeklySchedules::class, 'class_room_id');
    }

    protected static function booted()
    {
        static::saving(function (ClassRooms $room) {
            // Normalize and auto-generate code when not provided
            if (empty($room->code) && !empty($room->name)) {
                // Create a compact uppercase code from name
                $base = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper($room->name));
                $candidate = $base;
                $i = 1;
                while (self::where('code', $candidate)->where('id', '!=', $room->id ?? 0)->exists()) {
                    $candidate = $base . $i;
                    $i++;
                }
                $room->code = $candidate;
            } elseif (!empty($room->code)) {
                $room->code = strtoupper($room->code);
            }
        });
    }
}
