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
        'total_kg',
        'lama_packing',
        'productivity',
    ];
 
    protected $casts = [
        'total_kg' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'lama_packing' => 'decimal:2',
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
