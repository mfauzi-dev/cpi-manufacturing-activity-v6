<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'total_hours_actual',
        'total_hours_konversi',
        'hourly_rate',
        'overtime_amount',
        'status',
        'description',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours_actual' => 'decimal:2',
        'total_hours_konversi' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}