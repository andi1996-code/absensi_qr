<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teachers extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'nip',
        'qr_code',
        'email',
        'phone',
        'position',
        'photo_path',
        'salary_notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all weekly schedules for this teacher
     */
    public function weeklySchedules(): HasMany
    {
        return $this->hasMany(WeeklySchedules::class, 'teacher_id');
    }

    /**
     * Get all duha attendances for this teacher
     */
    public function duhaAttendances(): HasMany
    {
        return $this->hasMany(DuhaAttendances::class, 'teacher_id');
    }

    /**
     * Get all departure attendances for this teacher
     */
    public function departureAttendances(): HasMany
    {
        return $this->hasMany(DepartureAttendances::class, 'teacher_id');
    }

    /**
     * Get all lesson attendances for this teacher
     */
    public function lessonAttendances(): HasMany
    {
        return $this->hasMany(LessonAttendances::class, 'teacher_id');
    }

    /**
     * Get all salaries for this teacher
     */
    public function salaries(): HasMany
    {
        return $this->hasMany(Salaries::class, 'teacher_id');
    }

    /**
     * Accessor to provide a unified `photo` attribute that maps to `photo_path`.
     * This allows code to use either `$teacher->photo` or `$teacher->photo_path`.
     */
    public function getPhotoAttribute(): ?string
    {
        return $this->photo_path;
    }
}
