<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'code',
        'name'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function psGroups()
    {
        return $this->hasMany(PsGroup::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
