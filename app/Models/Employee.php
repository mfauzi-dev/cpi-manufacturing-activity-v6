<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'outsourcing_id',
        'cost_center_id',
        'ps_group_id',
        'position_id',
        'department_id',
        'nik',
        'name',
        'employment_status',
        'personel_area',
        'employee_status',
        'gender',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public function outsourcing()
    {
        return $this->belongsTo(Outsourcing::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function psGroup()
    {
        return $this->belongsTo(PsGroup::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function dailyActivities()
    {
        return $this->hasMany(DailyActivity::class);
    }

    public function penggajianHarians()
    {
        return $this->hasMany(PenggajianHarian::class);
    }
}
