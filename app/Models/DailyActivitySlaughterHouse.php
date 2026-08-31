<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivitySlaughterHouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'department_id',
        'cost_center_id',
        'ps_group_id',
        'product_group_id',
        'line_id',
        'employee_id',
        'input_by',
    ];
 
    protected $casts = [
        'tanggal' => 'date',
    ];
 
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
 
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
 
    public function psGroup()
    {
        return $this->belongsTo(PsGroup::class);
    }
 
    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }
 
    public function line()
    {
        return $this->belongsTo(Line::class);
    }
 
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
 
    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
 
    public function details()
    {
        return $this->hasMany(DailyActivityDetailSlaughterHouse::class, 'daily_activity_slaughter_house_id');
    }

    public function getTotalGajiAttribute()
    {
        return $this->details->sum('total_harga');
    }
}
