<?php

namespace Database\Seeders;

use App\Models\CostCenter;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sausage = Department::where('name', 'Sausage')->first();
        $slaughterHouse = Department::where('name', 'Slaughter House')->first();
        $furtherProcessing = Department::where('name', 'Further Processing')->first();

        CostCenter::insert([
            [
                'department_id' => $sausage->id,
                'code' => '921',
                'name' => 'Meat Preperation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $slaughterHouse->id,
                'code' => '901',
                'name' => 'Defeataring',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => $furtherProcessing->id,
                'code' => '911',
                'name' => 'Further',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
