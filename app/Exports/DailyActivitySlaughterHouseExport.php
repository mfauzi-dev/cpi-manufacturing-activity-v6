<?php

namespace App\Exports;

use App\Models\DailyActivityDetailSlaughterHouse;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyActivitySlaughterHouseExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $costCenterId;
    protected $psGroupId;
    protected $fromDate;
    protected $toDate;
    protected $departmentId;
    protected $lineId;

    public function __construct(
        $costCenterId,
        $psGroupId,
        $fromDate,
        $toDate,
        $departmentId = null,
        $lineId = null
    ) {
        $this->costCenterId = $costCenterId;
        $this->psGroupId = $psGroupId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->departmentId = $departmentId;
        $this->lineId = $lineId;
    }

    public function query()
    {
        return DailyActivityDetailSlaughterHouse::query()
            ->with([
                'product',
                'dailyActivitySlaughterHouse.employee',
                'dailyActivitySlaughterHouse.inputBy',
                'dailyActivitySlaughterHouse.line',
            ])
            ->whereHas('dailyActivitySlaughterHouse', function ($q) {
                $q->where('cost_center_id', $this->costCenterId)
                    ->where('ps_group_id', $this->psGroupId)
                    ->whereBetween('tanggal', [
                        $this->fromDate,
                        $this->toDate
                    ]);

                if ($this->departmentId) {
                    $q->where('department_id', $this->departmentId);
                }

                if ($this->lineId) {
                    $q->where('line_id', $this->lineId);
                }
            })
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->orderBy('daily_activity_slaughter_houses.tanggal')
            ->select('daily_activity_detail_slaughter_houses.*');
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
            'Harga/kg',
            'Rupiah',
            'Diinput Oleh',
        ];
    }

    public function map($row): array
    {
        $dailyActivity = $row->dailyActivitySlaughterHouse;

        return [
            $dailyActivity->tanggal
                ? $dailyActivity->tanggal->format('d M Y')
                : '-',

            $dailyActivity->line->name ?? '-',

            $row->product->material_code ?? '-',

            $row->product->material_name ?? '-',

            $dailyActivity->employee->name ?? '-',

            (float) $row->total_kg,

            (float) $row->harga_per_kg,

            (float) $row->total_harga,

            $dailyActivity->inputBy->name ?? '-',
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