<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_activity_id',
        'product_id',
        'total_kg',
        'harga_per_kg',
        'total_harga',
        'lama_packing',
    ];
 
    protected $casts = [
        'total_kg'     => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_harga'  => 'decimal:2',
        'lama_packing'  => 'decimal:2',
    ];
 
    public function dailyActivity()
    {
        return $this->belongsTo(DailyActivity::class);
    }
 
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
