<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProduction extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'tanggal',
        'department_id',
        'cost_center_id',
        'ps_group_id',
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
 
    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
 
    public function details()
    {
        return $this->hasMany(DailyProductionDetail::class);
    }
}
