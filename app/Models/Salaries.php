<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salaries extends Model
{
    protected $table = 'salaries';

    protected $fillable = [
        'teacher_id',
        'year',
        'month',
        'total_scheduled_hours',
        'attended_hours',
        'absent_hours',
        'total_amount',
        'additional_amount',
        'additional_notes',
        'deductions_amount',
        'deductions_notes',
        'is_paid',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_scheduled_hours' => 'integer',
        'attended_hours' => 'integer',
        'absent_hours' => 'integer',
        'total_amount' => 'integer',
        'additional_amount' => 'integer',
        'deductions_amount' => 'integer',
        'is_paid' => 'boolean',
    ];

    /**
     * Get the teacher that owns this salary
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teachers::class, 'teacher_id');
    }

    /**
     * Get month name in Indonesian
     */
    public function getMonthName(): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$this->month] ?? 'Unknown';
    }

    /**
     * Get attendance percentage
     */
    public function getAttendancePercentage(): float
    {
        if ($this->total_scheduled_hours === 0) {
            return 0;
        }

        return round(($this->attended_hours / $this->total_scheduled_hours) * 100, 2);
    }

    /**
     * Format total amount as currency
     */
    public function getFormattedAmount(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }
}
