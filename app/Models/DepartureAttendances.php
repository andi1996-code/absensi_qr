<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartureAttendances extends Model
{
    protected $table = 'departure_attendances';

    // Disable timestamps karena tabel tidak memiliki created_at & updated_at
    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'date',
        'scanned_at',
        'is_late',
    ];

    protected $casts = [
        'date' => 'date',
        'scanned_at' => 'datetime',
        'is_late' => 'integer',
    ];

    /**
     * Get the teacher that owns this attendance
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teachers::class, 'teacher_id');
    }
}
