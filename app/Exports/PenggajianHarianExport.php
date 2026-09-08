<?php

namespace App\Exports;

use App\Models\PenggajianHarian;
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

class PenggajianHarianExport implements
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
    protected $outsourcingId;
    protected int $no = 0;

    public function __construct(
        int $month,
        int $year,
        ?int $departmentId = null,
        $outsourcingId = null
    ) {
        $this->month = $month;
        $this->year = $year;
        $this->departmentId = $departmentId;
        $this->outsourcingId = $outsourcingId;
    }

    public function collection()
    {
        $query = PenggajianHarian::with([
            'employee',
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year);

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        if ($this->outsourcingId) {
            $query->whereHas('employee', function ($q) {
                $q->where('outsourcing_id', $this->outsourcingId);
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
            'NIK',
            'NAMA',
            'DEPARTMENT',
            'OUTSOURCING',
            'UMP',
            'STANDAR HARI KERJA',
            'TOTAL HARI KERJA',
            'UPAH HARIAN',
            'TOTAL OVERTIME',
            'JAMSOSTEK (4.89%)',
            'BPJS KESEHATAN (4%)',
            'BPJS PENSIUN (2%)',
            'MANAGEMEN FEE (175000/25)',
            'GAJI BERSIH',
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
            $payroll->employee->department->name ?? '-',
            $payroll->employee->outsourcing->name ?? '-',
            (string) ($payroll->ump_used ?? 0),
            (string) ($payroll->hari_kerja_standar_used ?? 0),
            (string) ($payroll->work_days ?? 0),
            (string) ($payroll->upah_harian ?? 0),
            (string) ($payroll->overtime_total ?? 0),
            (string) ($payroll->jamsostek ?? 0),
            (string) ($payroll->bpjs_kesehatan ?? 0),
            (string) ($payroll->bpjs_pensiun ?? 0),
            (string) ($payroll->managemen_fee ?? 0),
            (string) ($payroll->net_salary ?? 0),
            (string) ($payroll->grand_total_upah ?? 0),
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
            'F' => 20,
            'G' => 18,
            'H' => 20,
            'I' => 18,
            'J' => 18,
            'K' => 18,
            'L' => 20,
            'M' => 18,
            'N' => 28,
            'O' => 25,
            'P' => 25,
            'Q' => 28,
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

                $lastDataRow = $sheet->getHighestRow();
                $grandTotalRow = $lastDataRow + 1;

                $sheet->mergeCells("A{$grandTotalRow}:F{$grandTotalRow}");

                $sheet->setCellValue(
                    "A{$grandTotalRow}",
                    'GRAND TOTAL'
                );

                $sheet->setCellValue(
                    "G{$grandTotalRow}",
                    '-'
                );

                $sheet->setCellValue(
                    "H{$grandTotalRow}",
                    '-'
                );

                $sheet->setCellValue(
                    "I{$grandTotalRow}",
                    "=SUM(I2:I{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "J{$grandTotalRow}",
                    "=SUM(J2:J{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "K{$grandTotalRow}",
                    "=SUM(K2:K{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "L{$grandTotalRow}",
                    "=SUM(L2:L{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "M{$grandTotalRow}",
                    "=SUM(M2:M{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "N{$grandTotalRow}",
                    "=SUM(N2:N{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "O{$grandTotalRow}",
                    "=SUM(O2:O{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "P{$grandTotalRow}",
                    "=SUM(P2:P{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "Q{$grandTotalRow}",
                    "=SUM(Q2:Q{$lastDataRow})"
                );

                $sheet->getStyle("A1:Q{$grandTotalRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );

                $sheet->getStyle("A1:Q{$grandTotalRow}")
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet->getStyle("A2:A{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("H2:I{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("G2:Q{$grandTotalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("A{$grandTotalRow}:Q{$grandTotalRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("A{$grandTotalRow}:F{$grandTotalRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("G{$grandTotalRow}:Q{$grandTotalRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                $sheet->getRowDimension($grandTotalRow)
                    ->setRowHeight(25);
            },
        ];
    }
}