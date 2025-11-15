<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTime extends Model
{
    protected $table = 'schedule_times';

    protected $fillable = [
        'hour_number',
        'start_time',
        'end_time',
        'label',
        'is_lesson',
    ];

    protected $casts = [
        'is_lesson' => 'boolean',
    ];

    protected $appends = ['duration_minutes'];

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute()
    {
        try {
            $startStr = is_string($this->start_time) ? $this->start_time : $this->start_time->format('H:i:s');
            $endStr = is_string($this->end_time) ? $this->end_time : $this->end_time->format('H:i:s');

            $start = \Carbon\Carbon::createFromFormat('H:i:s', substr($startStr, 0, 8));
            $end = \Carbon\Carbon::createFromFormat('H:i:s', substr($endStr, 0, 8));

            $minutes = $end->diffInMinutes($start);
            return abs($minutes); // Always return positive
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get formatted start time
     */
    public function getFormattedStartTimeAttribute()
    {
        try {
            $timeStr = is_string($this->start_time) ? $this->start_time : $this->start_time->format('H:i:s');
            return substr($timeStr, 0, 5); // Get HH:MM only
        } catch (\Exception $e) {
            return '--:--';
        }
    }

    /**
     * Get formatted end time
     */
    public function getFormattedEndTimeAttribute()
    {
        try {
            $timeStr = is_string($this->end_time) ? $this->end_time : $this->end_time->format('H:i:s');
            return substr($timeStr, 0, 5); // Get HH:MM only
        } catch (\Exception $e) {
            return '--:--';
        }
    }
}
