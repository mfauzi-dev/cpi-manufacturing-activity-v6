<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'jumlah_hk',
        'shift_id',
        'keterangan_izin',
        'input_by',
        'line_id',
    ];

    protected $casts = [
        'date' => 'date',
        'jumlah_hk' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}