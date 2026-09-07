<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenggajianKaryawanTetap extends Model
{

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'basic_salary',
        'overtime_total',
        'grand_total_salary',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'basic_salary' => 'decimal:2',
        'overtime_total' => 'decimal:2',
        'grand_total_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}