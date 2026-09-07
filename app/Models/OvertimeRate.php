<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'employee_status',
        'level_id',
        'position_id',
        'rate',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'rate' => 'decimal:2',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}