<?php

namespace App\Exports;

use App\Models\DailyProductionDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyProductionExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $costCenterId;
    protected $psGroupId;
    protected $fromDate;
    protected $toDate;

    public function __construct($costCenterId, $psGroupId, $fromDate, $toDate)
    {
        $this->costCenterId = $costCenterId;
        $this->psGroupId    = $psGroupId;
        $this->fromDate     = $fromDate;
        $this->toDate       = $toDate;
    }

    public function query()
    {
        return DailyProductionDetail::query()
            ->with(['product', 'dailyProduction.inputBy'])
            ->whereHas('dailyProduction', function ($q) {
                $q->where('cost_center_id', $this->costCenterId)
                  ->where('ps_group_id', $this->psGroupId)
                  ->whereBetween('tanggal', [$this->fromDate, $this->toDate]);
            })
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->orderBy('daily_productions.tanggal')
            ->select('daily_production_details.*');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Material',
            'Nama Material',
            'Kg',
            'Harga/kg',
            'Rupiah',
            'Diinput Oleh',
        ];
    }

    public function map($row): array
    {
        $dp = $row->dailyProduction;

        return [
            $dp->tanggal->format('d M Y'),
            $row->product->material_code ?? '-',
            $row->product->material_name ?? '-',
            (float) $row->total_kg,
            (float) $row->harga_per_kg,
            (float) $row->total_harga,
            $dp->inputBy->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}