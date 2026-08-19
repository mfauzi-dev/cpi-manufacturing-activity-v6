<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'department_id',
        'cost_center_id',
        'ps_group_id',
        'input_by',
        'employee_id'
    ];
 
    protected $casts = [
        'tanggal' => 'date',
    ];
 
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
        return $this->hasMany(DailyActivityDetail::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    
}
