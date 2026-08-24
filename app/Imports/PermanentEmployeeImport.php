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
    protected $employmentStatus;

    public function __construct($employmentStatus)
    {
        $this->employmentStatus = $employmentStatus;
    }
    
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
            'department',
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
            $nik = trim($row['nomor_karyawan'] ?? '');
            $name = trim($row['nama'] ?? '');
            $costCenterName = trim($row['cost_center'] ?? '');
            $departmentName = trim($row['department'] ?? '');
            $psGroupName = trim($row['ps_group'] ?? '');

            if (empty($nik) && empty($name)) {
                continue;
            }

            $costCenter = null;

            if (!empty($costCenterName)) {
                $costCenter = CostCenter::where('name', $costCenterName)->first();
            }

            $psGroup = null;

            if (!empty($psGroupName)) {
                $psGroup = PsGroup::where('name', $psGroupName)->first();
            }

            $department = null;

            if (!empty($departmentName)) {
                $department = Department::where('name', $departmentName)->first();
            }

            $data = [
                'nik' => $nik ?: null,
                'name' => $name,

                'cost_center_id' => $costCenter?->id,

                'ps_group_id' => $psGroup?->id,

                'position_id' => null,

                'department_id' => $department?->id,

                'employment_status' => $this->employmentStatus,

                'employee_status' => 'cpi',

                'personel_area' => null,

                'gender' => trim($row['gender'] ?? ''),
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