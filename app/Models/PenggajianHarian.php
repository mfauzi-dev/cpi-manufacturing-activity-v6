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
        'net_salary',
    ];

    protected $casts = [
        'ump_used'    => 'decimal:2',
        'upah_harian' => 'decimal:2',
        'net_salary'  => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
