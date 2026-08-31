<?php

namespace App\Exports;

use App\Models\PenggajianBorongan;
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

class PenggajianBoronganExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    protected int $month;
    protected int $year;
    protected ?int $departmentId;
    protected int $no = 0;

    public function __construct(
        int $month,
        int $year,
        ?int $departmentId = null
    ) {
        $this->month = $month;
        $this->year = $year;
        $this->departmentId = $departmentId;
    }

    public function collection()
    {
        $query = PenggajianBorongan::with('employee')
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->whereHas('employee', function ($q) {
                $q->where('employee_status', 'borongan');
            });

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        return $query
            ->orderBy('employee_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NO. KTP',
            'NIK AML',
            'NAMA',
            'HASIL PROSES (Kg)/Jam',
            'TOTAL HARI',
            'TOTAL UPAH YANG DITERIMA',
            'JAMSOSTEK (4.89%)',
            'BPJS KESEHATAN (4%)',
            'BPJS PENSIUN (2%)',
            'MANAGEMEN FEE (6800 * per Hari Kerja)',
            'GRAND TOTAL UPAH DITERIMA',
        ];
    }

    public function map($payroll): array
    {
        $this->no++;

        return [
            $this->no,
            $payroll->employee->ktp_number ?? '-',
            $payroll->employee->nik ?? '-',
            $payroll->employee->name ?? '-',
            (float) ($payroll->productivity ?? 0),
            (int) ($payroll->total_hari_kerja ?? 0),
            (float) ($payroll->total_upah ?? 0),
            (float) ($payroll->jamsostek ?? 0),
            (float) ($payroll->bpjs_kesehatan ?? 0),
            (float) ($payroll->bpjs_pensiun ?? 0),
            (float) ($payroll->managemen_fee ?? 0),
            (float) ($payroll->grand_total_upah ?? 0),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 14,
            'D' => 25,
            'E' => 20,
            'F' => 12,
            'G' => 22,
            'H' => 18,
            'I' => 20,
            'J' => 18,
            'K' => 28,
            'L' => 25,
        ];
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
                $lastColumn = 'L';

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("E2:E{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("G2:L{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("A2:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("F2:F{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}