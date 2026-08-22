<?php

namespace App\Exports;

use App\Models\Employee;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceSummaryExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    protected $month;
    protected $year;
    protected $outsourcingId;
    protected $costCenterId;
    protected $psGroupId;
    protected $search;

    public function __construct(
        $month,
        $year,
        $outsourcingId = null,
        $costCenterId = null,
        $psGroupId = null,
        $search = null
    ) {
        $this->month = $month;
        $this->year = $year;
        $this->outsourcingId = $outsourcingId;
        $this->costCenterId = $costCenterId;
        $this->psGroupId = $psGroupId;
        $this->search = $search;
    }

    public function query()
    {
        $startDate = Carbon::create(
            $this->year,
            $this->month,
            1
        )->startOfMonth();

        $endDate = Carbon::create(
            $this->year,
            $this->month,
            1
        )->endOfMonth();

        $query = Employee::query()
            ->with([
                'department',
                'outsourcing',
                'psGroup',
            ])

            ->withCount([
                'attendances as total_hadir' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'hadir');
                },

                'attendances as total_izin' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'izin');
                },

                'attendances as total_sakit' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'sakit');
                },

                'attendances as total_cuti' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'cuti');
                },

                // DATABASE KAMU: alfa
                'attendances as total_alfa' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'alfa');
                },
            ]);

        // Filter Outsourcing
        if ($this->outsourcingId) {
            $query->where('outsourcing_id', $this->outsourcingId);
        }

        // Filter Cost Center
        if ($this->costCenterId) {
            $query->where('cost_center_id', $this->costCenterId);
        }

        // Filter PS Group
        if ($this->psGroupId) {
            $query->where('ps_group_id', $this->psGroupId);
        }

        // Search NIK / Nama
        if ($this->search) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama',
            'Department',
            'OS',
            'Group',
            'Hadir',
            'Izin',
            'Sakit',
            'Cuti',
            'Alfa',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->nik,
            $employee->name,
            $employee->department->name ?? '-',
            $employee->outsourcing->name ?? '-',
            $employee->psGroup->name ?? '-',
            (string) ($employee->total_hadir ?? 0),
            (string) ($employee->total_izin ?? 0),
            (string) ($employee->total_sakit ?? 0),
            (string) ($employee->total_cuti ?? 0),
            (string) ($employee->total_alfa ?? 0),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}