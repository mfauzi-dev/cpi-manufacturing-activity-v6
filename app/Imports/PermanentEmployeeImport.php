<?php

namespace App\Imports;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PsGroup;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
            'status',
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
            $gender = trim($row['gender'] ?? '');
            $status = strtolower(trim($row['status'] ?? ''));

            if (empty($nik) && empty($name)) {
                continue;
            }

            if ($status === 'active') {
                $isActive = 1;
            } elseif ($status === 'tidak active') {
                $isActive = 0;
            } else {
                throw ValidationException::withMessages([
                    'status' => "Baris \"{$name}\": Status \"{$status}\" tidak valid. Gunakan \"active\" atau \"tidak active\".",
                ]);
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
                'gender' => $gender,
                'is_active' => $isActive,
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