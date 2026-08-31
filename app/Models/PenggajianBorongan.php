<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggajianBorongan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'total_kg',
        'total_hari_kerja',
        'total_upah',
        'jamsostek',
        'bpjs_kesehatan',
        'bpjs_pensiun',
        'managemen_fee',
        'grand_total_upah',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'total_kg' => 'decimal:2',
        'total_hari_kerja' => 'integer',
        'total_upah' => 'decimal:2',
        'jamsostek' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_pensiun' => 'decimal:2',
        'managemen_fee' => 'decimal:2',
        'grand_total_upah' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
