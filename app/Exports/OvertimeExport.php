<?php

namespace App\Exports;

use App\Models\Overtime;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class OvertimeExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    protected ?int $departmentId;
    protected ?int $costCenterId;
    protected ?string $status;
    protected ?string $overtimeType;
    protected ?string $dateFrom;
    protected ?string $dateTo;
    protected ?string $search;
    protected string $departmentName;
    protected string $roleName;
    protected int $no = 0;

    public function __construct(
        ?int $departmentId = null,
        ?int $costCenterId = null,
        ?string $status = null,
        ?string $overtimeType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        string $departmentName = '',
    ) {
        $this->departmentId = $departmentId;
        $this->costCenterId = $costCenterId;
        $this->status = $status;
        $this->overtimeType = $overtimeType;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->search = $search;

        $user = auth()->user();
        $this->departmentName = strtolower(trim($user->department?->name ?? ''));
        $this->roleName = strtolower(trim($user->role?->name ?? ''));
    }

    private function canSeeAmount()
    {
        return $this->departmentName === 'personalia dan general affair'
            && in_array($this->roleName, ['manager', 'general manager']);
    }

    public function collection()
    {
        $query = Overtime::with([
            'employee',
            'employee.department',
            'employee.costCenter',
            'approver',
        ]);

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        if ($this->costCenterId) {
            $query->whereHas('employee', function ($q) {
                $q->where('cost_center_id', $this->costCenterId);
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->overtimeType) {
            $query->where('overtime_type', $this->overtimeType);
        }

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->search) {
            $search = $this->search;

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('date')
            ->orderBy('employee_id')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'NO',
            'NIK',
            'NAMA',
            'DEPARTMENT',
            'COST CENTER',
            'JENIS OVERTIME',
            'TANGGAL',
            'TANGGAL FORMAT',
            'JAM MULAI',
            'JAM SELESAI',
            'TOTAL JAM AKTUAL',
            'TOTAL JAM KONVERSI',
        ];

        if ($this->canSeeAmount()) {
            $headings[] = 'RATE PER JAM';
            $headings[] = 'TOTAL OVERTIME';
        }

        $headings[] = 'STATUS';
        $headings[] = 'DISETUJUI OLEH';
        $headings[] = 'TANGGAL APPROVE';
        $headings[] = 'KETERANGAN';

        return $headings;
    }

    public function map($overtime): array
    {
        $this->no++;

        $jenisOvertime = match ($overtime->overtime_type) {
            'OTL1' => 'OTL1 - Hari Biasa Bulan Lalu',
            'OTL2' => 'OTL2 - Hari Libur Bulan Lalu',
            'OT01' => 'OT01 - Hari Biasa Bulan Ini',
            'OT02' => 'OT02 - Hari Libur Bulan Ini',
            default => $overtime->overtime_type ?? '-',
        };

        $data = [
            $this->no,
            $overtime->employee->nik ?? '-',
            $overtime->employee->name ?? '-',
            $overtime->employee->department->name ?? '-',
            $overtime->employee->costCenter->name ?? '-',
            $jenisOvertime,
            $overtime->date
                ? Carbon::parse($overtime->date)->format('d/m/Y')
                : '-',
            $overtime->date
                ? Carbon::parse($overtime->date)->format('dmY')
                : '-',
            $overtime->start_time ?? '-',
            $overtime->end_time ?? '-',
            (string) ($overtime->total_hours_actual ?? 0),
            (string) ($overtime->total_hours_konversi ?? 0),
        ];

        if ($this->canSeeAmount()) {
            $data[] = (string) ($overtime->hourly_rate ?? 0);
            $data[] = (string) ($overtime->overtime_amount ?? 0);
        }

        $data[] = $overtime->status ?? '-';
        $data[] = $overtime->approver->name ?? '-';
        $data[] = $overtime->approved_at
            ? Carbon::parse($overtime->approved_at)->format('d/m/Y H:i')
            : '-';
        $data[] = $overtime->description ?? '-';

        return $data;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,
            'B' => 14,
            'C' => 25,
            'D' => 20,
            'E' => 20,
            'F' => 32,
            'G' => 14,
            'H' => 18,
            'I' => 12,
            'J' => 12,
            'K' => 16,
            'L' => 18,
        ];

        if ($this->canSeeAmount()) {
            $widths['M'] = 16;
            $widths['N'] = 18;
            $widths['O'] = 14;
            $widths['P'] = 20;
            $widths['Q'] = 18;
            $widths['R'] = 25;
        } else {
            $widths['M'] = 14;
            $widths['N'] = 20;
            $widths['O'] = 18;
            $widths['P'] = 25;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $this->canSeeAmount() ? 'R' : 'P';

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("K2:L{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("A2:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("G2:J{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("H2:H{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('@');

                if ($this->canSeeAmount()) {
                    $sheet->getStyle("M2:N{$lastRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');

                    $sheet->getStyle("O2:O{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    $sheet->getStyle("M2:M{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}