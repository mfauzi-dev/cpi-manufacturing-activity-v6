<?php

namespace Database\Seeders;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $generalManagerRole = Role::where('name', 'General Manager')->first();
        $adminProductionRole = Role::where('name', 'Admin Production')->first();

        $sausage = Department::where('name', 'Sausage')->first();
        $slaughterHouse = Department::where('name', 'Slaughter House')->first();

        User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'department_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Manager',
                'email' => 'generalmanager@mail.com',
                'password' => Hash::make('password'),
                'role_id' => $generalManagerRole->id,
                'department_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin sausage',
                'email' => 'sausage@mail.com',
                'password' => Hash::make('password'),
                'role_id' => $adminProductionRole->id,
                'department_id' => $sausage->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin slaughterhouse',
                'email' => 'slaughterhouse@mail.com',
                'password' => Hash::make('password'),
                'role_id' => $adminProductionRole->id,
                'department_id' => $slaughterHouse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
