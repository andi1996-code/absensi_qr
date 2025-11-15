<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $table = 'school_profile';

    protected $fillable = [
        'name',
        'npsn',
        'address',
        'phone',
        'email',
        'logo_path',
        'header_text',
    ];

    protected $casts = [
        'name' => 'string',
        'npsn' => 'string',
        'address' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'logo_path' => 'string',
        'header_text' => 'string',
    ];
}
