<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggajianHarian extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'work_days',
        'ump_used',
        'hari_kerja_standar_used',
        'upah_harian',
        'jamsostek',
        'bpjs_kesehatan',
        'bpjs_pensiun',
        'managemen_fee',
        'grand_total_upah',
        'net_salary',
    ];

    protected $casts = [
        'ump_used' => 'decimal:2',
        'upah_harian' => 'decimal:2',
        'jamsostek' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_pensiun' => 'decimal:2',
        'managemen_fee' => 'decimal:2',
        'grand_total_upah' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
