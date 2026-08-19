<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['material_code', 'material_name', 'department_id', 'cost_center_id', 'product_group_id', 'harga_per_kg'];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id');
    }
    
}
