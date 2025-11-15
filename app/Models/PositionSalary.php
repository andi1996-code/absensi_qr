<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionSalary extends Model
{
    protected $fillable = [
        'position',
        'salary_adjustment',
        'description',
        'is_active',
    ];

    protected $casts = [
        'salary_adjustment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the formatted salary adjustment
     */
    public function getFormattedSalaryAttribute()
    {
        return 'Rp ' . number_format($this->salary_adjustment, 0, ',', '.');
    }

    /**
     * Scope untuk hanya jabatan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
