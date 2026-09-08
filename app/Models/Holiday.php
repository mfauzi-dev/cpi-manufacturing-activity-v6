<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'type',
        'is_joint_leave',
    ];

    protected $casts = [
        'date' => 'date',
        'is_joint_leave' => 'boolean',
    ];
}
