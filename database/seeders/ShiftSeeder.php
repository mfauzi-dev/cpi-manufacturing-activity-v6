<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();

        foreach ($departments as $department) {
            Shift::updateOrCreate(
                [
                    'department_id' => $department->id,
                    'name' => 'Shift 1',
                ],
            );

            Shift::updateOrCreate(
                [
                    'department_id' => $department->id,
                    'name' => 'Shift 2',
                ],
            );

            Shift::updateOrCreate(
                [
                    'department_id' => $department->id,
                    'name' => 'Shift 3',
                ],
                [
                    'jam_masuk' => '23:00:00',
                    'jam_keluar' => '07:00:00',
                ]
            );
        }
    }
}