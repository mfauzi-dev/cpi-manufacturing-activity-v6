<?php

namespace App\Exports;

use App\Models\DailyActivityDetailFurther;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyActivityFurtherIndexExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    protected $costCenterId;
    protected $psGroupId;
    protected $lineId;
    protected $fromDate;
    protected $toDate;
    protected $departmentId;

    public function __construct(
        $costCenterId = null,
        $psGroupId = null,
        $lineId = null,
        $fromDate = null,
        $toDate = null,
        $departmentId = null
    ) {
        $this->costCenterId = $costCenterId;
        $this->psGroupId = $psGroupId;
        $this->lineId = $lineId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->departmentId = $departmentId;
    }

    public function query()
    {
        $query = DailyActivityDetailFurther::query()
            ->with([
                'product',
                'dailyActivityFurther.employee',
                'dailyActivityFurther.inputBy',
                'dailyActivityFurther.line',
            ])
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            );

        /*
        |--------------------------------------------------------------------------
        | Department
        |--------------------------------------------------------------------------
        */
        if ($this->departmentId) {
            $query->where(
                'daily_activity_furthers.department_id',
                $this->departmentId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cost Center
        |--------------------------------------------------------------------------
        | Kosong = semua Cost Center
        |--------------------------------------------------------------------------
        */
        if ($this->costCenterId) {
            $query->where(
                'daily_activity_furthers.cost_center_id',
                $this->costCenterId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PS Group
        |--------------------------------------------------------------------------
        | Kosong = semua PS Group
        |--------------------------------------------------------------------------
        */
        if ($this->psGroupId) {
            $query->where(
                'daily_activity_furthers.ps_group_id',
                $this->psGroupId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Line
        |--------------------------------------------------------------------------
        | Kosong = semua Line
        |--------------------------------------------------------------------------
        */
        if ($this->lineId) {
            $query->where(
                'daily_activity_furthers.line_id',
                $this->lineId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tanggal
        |--------------------------------------------------------------------------
        */
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween(
                'daily_activity_furthers.tanggal',
                [
                    $this->fromDate,
                    $this->toDate,
                ]
            );
        } elseif ($this->fromDate) {
            $query->whereDate(
                'daily_activity_furthers.tanggal',
                '>=',
                $this->fromDate
            );
        } elseif ($this->toDate) {
            $query->whereDate(
                'daily_activity_furthers.tanggal',
                '<=',
                $this->toDate
            );
        }

        return $query
            ->orderBy('daily_activity_furthers.tanggal')
            ->select('daily_activity_detail_furthers.*');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Line',
            'Kode Material',
            'Nama Material',
            'Nama Karyawan',
            'Kg',
            'Lama Packing',
            'Productivity',
            'Diinput Oleh',
        ];
    }

    public function map($row): array
    {
        $daf = $row->dailyActivityFurther;

        return [
            $daf?->tanggal
                ? $daf->tanggal->format('d M Y')
                : '-',

            $daf?->line?->name ?? '-',

            $row->product?->material_code ?? '-',

            $row->product?->material_name ?? '-',

            $daf?->employee?->name ?? '-',

            (float) $row->total_kg,

            (float) $row->lama_packing,

            (float) $row->productivity,

            $daf?->inputBy?->name ?? '-',
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