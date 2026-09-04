<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityDetailSlaughterHouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_activity_slaughter_house_id',
        'product_id',
        'total_kg',
        'lama_packing',
        'productivity',
        'productivity_actual',
        'harga_per_kg',
        'total_harga',
    ];
 
    protected $casts = [
        'total_kg'     => 'decimal:2',  
        'lama_packing' => 'decimal:2',
        'productivity' => 'decimal:2',
        'productivity_actual'  => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_harga'  => 'decimal:2',
    ];

    public function dailyActivitySlaughterHouse()
    {
        return $this->belongsTo(DailyActivitySlaughterHouse::class, 'daily_activity_slaughter_house_id');
    }
 
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
