<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
