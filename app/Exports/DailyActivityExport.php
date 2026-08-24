<?php

namespace App\Exports;

use App\Models\DailyActivityDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyActivityExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $costCenterId;
    protected $psGroupId;
    protected $fromDate;
    protected $toDate;
    protected $departmentId;

    public function __construct($costCenterId, $psGroupId, $fromDate, $toDate, $departmentId = null)
    {
        $this->costCenterId = $costCenterId;
        $this->psGroupId    = $psGroupId;
        $this->fromDate     = $fromDate;
        $this->toDate       = $toDate;
        $this->departmentId = $departmentId;
    }

    public function query()
    {
        return DailyActivityDetail::query()
            ->with(['product', 'dailyActivity.employee', 'dailyActivity.inputBy'])
            ->whereHas('dailyActivity', function ($q) {
                $q->where('cost_center_id', $this->costCenterId)
                  ->where('ps_group_id', $this->psGroupId)
                  ->whereBetween('tanggal', [$this->fromDate, $this->toDate]);
                if ($this->departmentId) {
                    $q->where(
                        'department_id',
                        $this->departmentId
                    );
                }
            })
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->orderBy('daily_activities.tanggal')
            ->select('daily_activity_details.*');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Material',
            'Nama Material',
            'Nama Karyawan',
            'Kg',
            'Lama Packing',
            'Productivity',
            'Harga/kg',
            'Rupiah',
            'Diinput Oleh',
        ];
    }

    public function map($row): array
    {
        $da = $row->dailyActivity;

        return [
            $da->tanggal->format('d M Y'),
            $row->product->material_code ?? '-',
            $row->product->material_name ?? '-',
            $da->employee->name ?? '-',
            (float) $row->total_kg,
            (float) $row->lama_packing,
            (float) $row->productivity,
            (float) $row->harga_per_kg,
            (float) $row->total_harga,
            $da->inputBy->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}