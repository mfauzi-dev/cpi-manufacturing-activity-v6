<?php

namespace Database\Seeders;

use App\Models\Outsourcing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutsourcingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Outsourcing::insert([
            ['name' => 'PT DFI'],
            ['name' => 'PT PGU'],
            ['name' => 'PT SMA'],
            ['name' => 'PT ABJ'],
            ['name' => 'PT AML'],
        ]);
    }
}
