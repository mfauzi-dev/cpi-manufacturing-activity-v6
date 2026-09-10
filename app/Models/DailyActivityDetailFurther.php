<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityDetailFurther extends Model
{
    use HasFactory;

   protected $fillable = [
        'daily_activity_further_id',
        'product_id',
        'total_kg_rm',
        'total_kg_fg',
        'man_power',
        'productivity',
    ];

    protected $casts = [
        'total_kg_rm' => 'decimal:2',
        'total_kg_fg' => 'decimal:2',
        'man_power' => 'decimal:2',
        'productivity' => 'decimal:2',
    ];

    public function dailyActivityFurther()
    {
        return $this->belongsTo(DailyActivityFurther::class, 'daily_activity_further_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
