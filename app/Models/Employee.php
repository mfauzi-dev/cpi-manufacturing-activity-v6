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
        'level_id',
        'department_id',
        'nik',
        'name',
        'employment_status',
        'personel_area',
        'employee_status',
        'gender',
        'is_active',
        'join_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
    ];

    public function outsourcing()
    {
        return $this->belongsTo(Outsourcing::class);
    }

    public function overtime()
    {
        return $this->hasMany(Overtime::class);
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

    // Relasi many-to-many ke DailyActivityFurther lewat pivot,
    // menggantikan hasMany lama (sudah tidak relevan sejak employee_id
    // di daily_activity_furthers digantikan tabel pivot).
    public function dailyActivityFurthers()
    {
        return $this->belongsToMany(
            DailyActivityFurther::class,
            'daily_activity_further_employees'
        )
            ->withPivot('jumlah_hk')
            ->withTimestamps();
    }

    public function dailyActivitySlaughterHouses()
    {
        return $this->hasMany(DailyActivitySlaughterHouse::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}