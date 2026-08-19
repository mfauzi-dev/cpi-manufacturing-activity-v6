<?php

namespace Database\Seeders;

use App\Models\CostCenter;
use App\Models\PsGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PsGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meatPreperation = CostCenter::where('name', 'Meat Preperation')->first();
        $defeataring = CostCenter::where('name', 'Defeataring')->first();

        PsGroup::insert([
            [
                'cost_center_id' => $meatPreperation->id,
                'name' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cost_center_id' => $defeataring->id,
                'name' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
