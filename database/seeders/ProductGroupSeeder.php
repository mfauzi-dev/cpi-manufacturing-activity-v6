<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ProductGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $sausage = Department::where('name', 'Sausage')->first();

        ProductGroup::insert([
            [
                'department_id' => $sausage->id,
                'name' => 'SOSIS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'BASO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'GYOZA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'RTG SOSIS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'RTG BASO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'RTG SIOMAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'SIOMAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $sausage->id,
                'name' => 'OTHERS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
