<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeProductivityExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $allDetails;

    public function __construct($allDetails)
    {
        $this->allDetails = $allDetails;
    }

    public function collection()
    {
        return $this->allDetails;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIK',
            'NAMA',
            'DEPARTMENT',
            'COST CENTER',
            'PRODUK',
            'TOTAL KG',
            'PRODUCTIVITY',
            'TOTAL RUPIAH',
        ];
    }

    public function map($detail): array
    {
        $departmentName = strtolower(
            trim(
                $detail->employee->department->name ?? ''
            )
        );

        $productivity = $departmentName === 'slaughter house'
            ? $detail->display_productivity_actual
            : $detail->display_productivity;

        return [
            $detail->activity_date
                ? \Carbon\Carbon::parse(
                    $detail->activity_date
                )->format('d M Y')
                : '-',

            $detail->employee->nik ?? '-',

            $detail->employee->name ?? '-',

            $detail->employee->department->name ?? '-',

            $detail->employee->costCenter
                ? $detail->employee->costCenter->code .
                    ' - ' .
                    $detail->employee->costCenter->name
                : '-',

            $detail->product->material_name ?? '-',

            (float) ($detail->total_kg ?? 0),

            $productivity !== null
                ? (float) $productivity
                : '-',

            $detail->display_total_harga !== null
                ? (float) $detail->display_total_harga
                : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true
                ]
            ],
        ];
    }
}