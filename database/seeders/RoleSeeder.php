<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Role::insert([
            [
                'name' => 'Admin',
                'can_access_all_departments' => true,
            ],
            [
                'name' => 'General Manager',
                'can_access_all_departments' => true,
            ],
            [
                'name' => 'Manager',
                'can_access_all_departments' => true,
            ],
            [
                'name' => 'Admin Production',
                'can_access_all_departments' => false,
            ],
        ]);
    }
}
