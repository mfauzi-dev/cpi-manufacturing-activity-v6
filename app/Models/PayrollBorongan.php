<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBorongan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'period_month', 'period_year',
        'work_days', 'total_kg', 'total_earning',
        'bpjs_kesehatan', 'jaminan_pensiun', 'jht', 'management_fee',
        'net_salary', 'status', 'generated_by', 'generated_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
