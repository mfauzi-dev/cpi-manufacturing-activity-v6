<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_center_id',
        'name'
    ];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
