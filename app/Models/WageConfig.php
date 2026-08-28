<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WageConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'ump',
        'hari_kerja_standar',
    ];

    protected $casts = [
        'ump' => 'decimal:2',
    ];
}
