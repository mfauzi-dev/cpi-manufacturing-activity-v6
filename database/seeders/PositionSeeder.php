<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Position::insert([
            [
                'name' => 'Operator', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Leader', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Supervisor', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Manager', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Admin', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'name' => 'Programmer', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);
    }
}
