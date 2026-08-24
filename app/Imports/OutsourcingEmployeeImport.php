<?php

namespace App\Imports;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Outsourcing;
use App\Models\Position;
use App\Models\PsGroup;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OutsourcingEmployeeImport implements ToCollection, WithHeadingRow
{
    protected $outsourcing;
    protected $employeeStatus;

    public function __construct(Outsourcing $outsourcing, string $employeeStatus)
    {
        $this->outsourcing = $outsourcing;
        $this->employeeStatus = $employeeStatus;
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
            'nama',
            'department',
            'ps_group',
            'cost_center'
        ];

        $headers = array_keys($rows->first()->toArray());

        foreach ($requiredHeaders as $header) {
            if (! in_array($header, $headers)) {
                throw new \Exception(
                    "Format Excel tidak sesuai. Kolom '{$header}' tidak ditemukan."
                );
            }
        }

        foreach ($rows as $row) {
            $costCenterName = trim($row['cost_center'] ?? '');
            $psGroupName = trim($row['ps_group'] ?? '');
            $departmentName = trim($row['department'] ?? '');
            $gender = trim($row['gender'] ?? '');
            $nik = trim($row['nomor_karyawan'] ?? '');
        
            $name = trim($row['nama'] ?? '');

            if (empty($name)) {
                continue;
            }

            $department = Department::where('name', $departmentName)->first();

            if (!$department) {
                throw ValidationException::withMessages([
                    'department' => "Baris \"{$name}\": Department \"{$departmentName}\" tidak ditemukan.",
                ]);
            }

            $costCenter = CostCenter::where('name', $costCenterName)
                ->where('department_id', $department->id)
                ->first();

            if (!$costCenter) {
                throw ValidationException::withMessages([
                    'cost_center' => "Baris \"{$name}\": Cost Center \"{$costCenterName}\" tidak ditemukan di department \"{$departmentName}\".",
                ]);
            }

            $psGroup = PsGroup::where('name', $psGroupName)
                ->where('cost_center_id', $costCenter->id)
                ->first();



            $data = [
                'outsourcing_id' => $this->outsourcing->id,

                'nik' => $nik ?: null,

                'name' => $name,

                'department_id' => $department->id,

                'cost_center_id' => $costCenter->id,

                'ps_group_id' => $psGroup->id,

                'employment_status' => 'outsourcing',

                'employee_status' => $this->employeeStatus,

                'gender' => $gender,
            ];

            if (!empty($nik)) {
                Employee::updateOrCreate(
                    [
                        'name' => $name,
                        'department_id' => $department->id,
                        'outsourcing_id' => $this->outsourcing->id,
                        'employee_status' => $this->employeeStatus,
                    ],
                    $data
                );
            } else {
                Employee::updateOrCreate(
                    [
                        'name' => $name,
                        'department_id' => $department->id,
                        'outsourcing_id' => $this->outsourcing->id,
                        'employee_status' => $this->employeeStatus,
                    ],
                    $data
                );
            }
        }
    }
}
