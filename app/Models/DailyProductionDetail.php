<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProductionDetail extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'daily_production_id',
        'product_id',
        'total_kg',
        'harga_per_kg',
        'total_harga',
    ];
 
    protected $casts = [
        'total_kg'     => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_harga'  => 'decimal:2',
    ];
 
    public function dailyProduction()
    {
        return $this->belongsTo(DailyProduction::class);
    }
 
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
