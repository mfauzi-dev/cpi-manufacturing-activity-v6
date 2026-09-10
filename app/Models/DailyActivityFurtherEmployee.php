<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityFurtherEmployee extends Model
{
    use HasFactory;

    protected $table = 'daily_activity_further_employees';

    protected $fillable = [
        'daily_activity_further_id',
        'employee_id',
        'jumlah_hk',
    ];

    protected $casts = [
        'jumlah_hk' => 'decimal:2',
    ];

    public function dailyActivityFurther()
    {
        return $this->belongsTo(
            DailyActivityFurther::class,
            'daily_activity_further_id'
        );
    }

    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }
}