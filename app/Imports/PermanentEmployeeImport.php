<?php

namespace App\Imports;

use App\Models\Area;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Outsourcing;
use App\Models\Position;
use App\Models\PsGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PermanentEmployeeImport implements ToCollection, WithHeadingRow
{

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel kosong.');
        }

        $requiredHeaders = [
            'nomor_karyawan',
            'nama',
            'employment_status',
            'personel_area',
            'ps_group',
            'cost_center',
            'department',
            'position',
            'gender',
        ];

        $headers = array_keys($rows->first()->toArray());

        foreach ($requiredHeaders as $header) {
            if (!in_array($header, $headers)) {
                throw new \Exception(
                    "Format Excel tidak sesuai. Kolom '{$header}' tidak ditemukan."
                );
            }
        }

        foreach ($rows as $row) {
            $nik            = trim($row['nomor_karyawan'] ?? '');
            $name           = trim($row['nama'] ?? '');
            $costCenterName = trim($row['cost_center'] ?? '');
            $psGroupName    = trim($row['ps_group'] ?? '');
            $positionName   = trim($row['position'] ?? '');
            $departmentName = trim($row['department'] ?? '');

            // skip kalau nomor_karyawan DAN nama dua-duanya kosong
            if (empty($nik) && empty($name)) {
                continue;
            }

            $costCenter = CostCenter::where('name', $costCenterName)->first();
            $psGroup    = PsGroup::where('name', $psGroupName)->first();
            $position   = Position::where('name', $positionName)->first();
            $department = Department::where('name', $departmentName)->first();

            $data = [
                'nik'               => $nik ?: null,
                'name'              => $name,

                'cost_center_id'    => $costCenter?->id,
                'ps_group_id'       => $psGroup?->id,
                'position_id'       => $position?->id,
                'department_id'     => $department?->id,

                'employment_status' => trim($row['employment_status'] ?? ''),
                'employee_status'   => 'cpi',
                'personel_area'     => trim($row['personel_area'] ?: null),

                'gender'            => trim($row['gender'] ?? ''),
            ];

            if (!empty($nik)) {
                Employee::updateOrCreate(
                    ['nik' => $nik],
                    $data
                );
            } else {
                Employee::updateOrCreate(
                    ['name' => $name],
                    $data
                );
            }
        }
    }
}