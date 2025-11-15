<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurationSetting extends Model
{
    protected $table = 'school_settings';

    protected $fillable = [
        'lesson_duration_minutes',
    ];

    protected $casts = [
        'lesson_duration_minutes' => 'integer',
    ];
}
