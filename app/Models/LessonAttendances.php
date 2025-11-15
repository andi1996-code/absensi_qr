<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAttendances extends Model
{
    protected $table = 'lesson_attendances';

    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'date',
        'hour_number',
        'scanned_at',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'hour_number' => 'integer',
        'scanned_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the teacher that owns this attendance
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teachers::class, 'teacher_id');
    }
}
