<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'can_access_all_departments',
    ];
 
    protected $casts = [
        'can_access_all_departments' => 'boolean',
    ];
 
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
