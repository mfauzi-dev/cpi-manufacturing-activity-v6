<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollHarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'standard_days',
        'work_days',
        'basic_salary',
        'management_fee',
        'bpjs_kesehatan',
        'jaminan_pensiun',
        'jht',
        'total_deduction',
        'status',
    ];
}
