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
            'JAMSOSTEK (4.89%)',
            'BPJS KESEHATAN (4%)',
            'BPJS PENSIUN (2%)',
            'MANAGEMEN FEE (6800 * per Hari Kerja)',
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
            'O' => 18,
            'P' => 25,
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
                $lastColumn = 'P';

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("G2:G{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("H2:I{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("J2:P{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle("A2:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("H2:I{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}