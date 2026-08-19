<?php

namespace App\Imports;

use App\Models\Area;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Outsourcing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceImport implements ToCollection, WithHeadingRow
{
    protected $date;
    protected $areaId;

    public function __construct($date, $areaId)
    {
        $this->date = $date;
        $this->areaId = $areaId;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $nik = trim($row['nik'] ?? '');

            if (!$nik) continue;

            $employee = Employee::where('nik', $nik)->first();
            if (!$employee) continue; // 👈 INI WAJIB

            $area = Area::where('name', trim($row['area'] ?? ''))->first();
            $outsourcing = Outsourcing::where('name', trim($row['os'] ?? ''))->first();

            // jangan pakai ->id kalau null
            if (!$area || !$outsourcing) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'attendance_date' => $this->date,
                ],
                [
                    'area_id' => $area->id,
                    'outsourcing_id' => $outsourcing->id,
                    'status' => $row['status'] ?? 'HADIR',
                    'overtime_hours' => $row['lembur'] ?? 0,
                    'shift_name' => $row['shift'] ?? null,
                ]
            );
        }
    }
}
