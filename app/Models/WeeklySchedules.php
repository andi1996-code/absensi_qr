<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklySchedules extends Model
{
    protected $table = 'weekly_schedules';

    protected $fillable = [
        'teacher_id',
        'day_of_week',
        'hour_number',
        'schedule_time_id',
        'class_room',
        'class_room_id',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'hour_number' => 'integer',
    ];

    public $timestamps = false;

    /**
     * Get the teacher that owns this schedule
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teachers::class, 'teacher_id');
    }

    /**
     * Get the schedule time
     */
    public function scheduleTime(): BelongsTo
    {
        return $this->belongsTo(ScheduleTime::class, 'schedule_time_id');
    }

    /**
     * Get the class room relation if available
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRooms::class, 'class_room_id');
    }

    protected static function booted()
    {
        static::saving(function (WeeklySchedules $schedule) {
            // If class_room_id is set, ensure class_room (string) matches the class name
            if ($schedule->class_room_id) {
                $classRoom = ClassRooms::find($schedule->class_room_id);
                $schedule->class_room = $classRoom?->name;
            }
        });
    }

    /**
     * Get day name in Indonesian
     */
    public function getDayName(): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }
}
