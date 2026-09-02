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
        'keterangan_izin',
        'input_by',
        'line_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];
 
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }
 
    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}
