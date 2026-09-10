<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityFurther extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'department_id',
        'cost_center_id',
        'ps_group_id',
        'line_id',
        'process_type_id',
        'input_by',
        // 'employee_id' SUDAH TIDAK DIPAKAI, digantikan relasi many-to-many employees()
        // jangan dihapus dari $fillable sampai migration drop kolom benar-benar dijalankan,
        // supaya kalau ada kode lama yang masih nulis employee_id tidak error MassAssignmentException.
        // Setelah migration drop kolom dijalankan, baris di atas boleh dihapus dari $fillable.
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

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function details()
    {
        return $this->hasMany(DailyActivityDetailFurther::class, 'daily_activity_further_id');
    }

    public function employees()
    {
        return $this->belongsToMany(
            Employee::class,
            'daily_activity_further_employees'
        )
            ->withPivot('jumlah_hk')
            ->withTimestamps();
    }
}