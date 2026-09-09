<?php

namespace App\Exports;

use App\Models\PenggajianHarian;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PenggajianHarianExport implements
    FromCollection,
    WithMapping,
    WithEvents
{
    protected int $month;
    protected int $year;
    protected ?int $departmentId;
    protected ?int $costCenterId;
    protected $outsourcingId;
    protected int $no = 0;

    /** @var Carbon[] semua tanggal dalam periode */
    protected array $dates = [];

    protected const IDENTITY_HEADERS = ['NO', 'NO. KTP', 'NIK', 'NAMA', 'DEPARTMENT', 'OUTSOURCING'];
    protected const DATE_SUB_HEADERS = ['JK', 'HK', 'OT', 'CON'];
    protected const HARI_KERJA_HEADERS = ['S', 'IZ', 'A', 'I', 'II', 'III'];
    protected const TOTALS_HEADERS = ['JK 7 JAM', 'JK 5 JAM', 'TOTAL JK', 'TOTAL HK'];
    protected const PAYROLL_HEADERS = [
        'UMP',
        'STANDAR HARI KERJA',
        'TOTAL HARI KERJA',
        'UPAH HARIAN',
        'TOTAL OVERTIME',
        'JAMSOSTEK (4,89%)',
        'BPJS KESEHATAN (4%)',
        'BPJS PENSIUN (2%)',
        'MANAGEMEN FEE (175000/25)',
        'GAJI BERSIH',
        'GRAND TOTAL UPAH DITERIMA',
    ];

    protected const HEADER_ROWS = 10; // baris 1-7 judul, baris 9-10 header tabel

    // Diisi otomatis saat buildTableHeader() jalan, dipakai lagi untuk
    // grand total & format angka supaya tidak hardcode huruf kolom.
    protected array $numericColumns = [];
    protected int $lastColumnIndex = 0;

    public function __construct(
        int $month,
        int $year,
        ?int $departmentId = null,
        ?int $costCenterId = null,
        $outsourcingId = null
    ) {
        $this->month = $month;
        $this->year = $year;
        $this->departmentId = $departmentId;
        $this->costCenterId = $costCenterId;
        $this->outsourcingId = $outsourcingId;

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $this->dates = iterator_to_array(
            CarbonPeriod::create($start, $end)
        );
    }

    public function collection()
    {
        $start = $this->dates[0]->format('Y-m-d');
        $end = end($this->dates)->format('Y-m-d');

        $query = PenggajianHarian::with([
            'employee',
            'employee.department',
            'employee.outsourcing',
            'employee.attendances' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end])->with('shift');
            },
            'employee.overtime' => function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
            },
        ])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year);

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

        if ($this->outsourcingId) {
            $query->whereHas('employee', function ($q) {
                $q->where('outsourcing_id', $this->outsourcingId);
            });
        }

        return $query->orderBy('employee_id')->get();
    }

    public function map($payroll): array
    {
        $this->no++;
        $employee = $payroll->employee;

        $attendanceByDate = $employee
            ? $employee->attendances->keyBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'))
            : collect();

        $overtimeByDate = $employee
            ? $employee->overtime->groupBy(fn ($o) => Carbon::parse($o->date)->format('Y-m-d'))
            : collect();

        $identity = [
            $this->no,
            $employee->ktp_number ?? '-',
            $employee->nik ?? '-',
            $employee->name ?? '-',
            $employee->department->name ?? '-',
            $employee->outsourcing->name ?? '-',
        ];

        $dateColumns = [];
        foreach ($this->dates as $date) {
            $key = $date->format('Y-m-d');
            $att = $attendanceByDate->get($key);
            $otForDate = $overtimeByDate->get($key, collect());

            $dateColumns[] = $att->jumlah_hk ?? '-';           // JK
            $dateColumns[] = $this->resolveHkDisplay($att);     // HK
            $dateColumns[] = (float) $otForDate->sum('total_hours_actual');    // OT
            $dateColumns[] = (float) $otForDate->sum('total_hours_konversi');  // CON
        }

        $summary = $this->computeSummary($attendanceByDate);

        $harikerja = [
            $summary['sakit'],
            $summary['izin'],
            $summary['alfa'],
            $summary['shiftI'],
            $summary['shiftII'],
            $summary['shiftIII'],
        ];

        $totalJk = $summary['jk7'] + $summary['jk5'];
        $totalHk = $summary['shiftI'] + $summary['shiftII'] + $summary['shiftIII'];

        $totals = [
            $summary['jk7'],
            $summary['jk5'],
            $totalJk,
            $totalHk,
        ];

        $payrollColumns = [
            (float) ($payroll->ump_used ?? 0),
            (float) ($payroll->hari_kerja_standar_used ?? 0),
            (float) ($payroll->work_days ?? 0),
            (float) ($payroll->upah_harian ?? 0),
            (float) ($payroll->overtime_total ?? 0),
            (float) ($payroll->jamsostek ?? 0),
            (float) ($payroll->bpjs_kesehatan ?? 0),
            (float) ($payroll->bpjs_pensiun ?? 0),
            (float) ($payroll->managemen_fee ?? 0),
            (float) ($payroll->net_salary ?? 0),
            (float) ($payroll->grand_total_upah ?? 0),
        ];

        return array_merge($identity, $dateColumns, $harikerja, $totals, $payrollColumns);
    }

    /**
     * Hitung total sakit/izin/alfa dan jumlah hari kerja per shift,
     * plus sum jumlah_hk untuk 7 jam dan 5 jam, dari seluruh attendance
     * employee di periode ini.
     */
    protected function computeSummary(SupportCollection $attendanceByDate): array
    {
        $result = [
            'sakit' => 0, 'izin' => 0, 'alfa' => 0,
            'shiftI' => 0, 'shiftII' => 0, 'shiftIII' => 0,
            'jk7' => 0.0, 'jk5' => 0.0,
        ];

        foreach ($attendanceByDate as $att) {
            $status = mb_strtolower($att->status ?? '');

            if (str_contains($status, 'sakit')) {
                $result['sakit']++;
                continue;
            }
            if (str_contains($status, 'izin')) {
                $result['izin']++;
                continue;
            }
            if (str_contains($status, 'alfa') || str_contains($status, 'alpha')) {
                $result['alfa']++;
                continue;
            }
            if (str_contains($status, 'off') || str_contains($status, 'libur')) {
                continue; // hari libur, tidak dihitung ke shift manapun
            }

            $roman = $this->shiftRoman($att->shift);
            if ($roman === 'I') {
                $result['shiftI']++;
            } elseif ($roman === 'II') {
                $result['shiftII']++;
            } elseif ($roman === 'III') {
                $result['shiftIII']++;
            }

            $jam = round((float) ($att->jumlah_hk ?? 0), 2);
            if ($jam === 7.0) {
                $result['jk7'] += 7;
            } elseif ($jam === 5.0) {
                $result['jk5'] += 5;
            }
        }

        return $result;
    }

    protected function resolveHkDisplay($att): string
    {
        if (! $att) {
            return '-';
        }

        $status = mb_strtolower($att->status ?? '');

        if (str_contains($status, 'sakit')) return 'S';
        if (str_contains($status, 'izin')) return 'IZ';
        if (str_contains($status, 'alfa') || str_contains($status, 'alpha')) return 'A';
        if (str_contains($status, 'off') || str_contains($status, 'libur')) return 'DO';

        return $this->shiftRoman($att->shift) ?? '-';
    }

    protected function shiftRoman($shift): ?string
    {
        if (! $shift || ! $shift->name) {
            return null;
        }

        $map = ['1' => 'I', '2' => 'II', '3' => 'III'];

        if (preg_match('/(\d)/', $shift->name, $m) && isset($map[$m[1]])) {
            return $map[$m[1]];
        }

        // Kalau nama shift memang sudah "I"/"II"/"III"
        return $shift->name;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $dataRowCount = $sheet->getHighestRow();

                $sheet->insertNewRowBefore(1, self::HEADER_ROWS);

                $this->buildTitleBlock($sheet);
                $this->buildTableHeader($sheet);

                $lastDataRow = self::HEADER_ROWS + $dataRowCount;
                $firstDataRow = self::HEADER_ROWS + 1;
                $grandTotalRow = $lastDataRow + 1;

                $this->buildGrandTotalRow($sheet, $firstDataRow, $lastDataRow, $grandTotalRow);
                $this->applyGeneralStyles($sheet, $firstDataRow, $lastDataRow, $grandTotalRow);
                $this->autoSizeColumns($sheet);
            },
        ];
    }

    protected function lastColumnLetter(): string
    {
        return Coordinate::stringFromColumnIndex($this->lastColumnIndex);
    }

    protected function buildTitleBlock(Worksheet $sheet): void
    {
        // lastColumnIndex belum dihitung di titik ini, jadi hitung dulu total kolom
        $this->lastColumnIndex = count(self::IDENTITY_HEADERS)
            + count($this->dates) * count(self::DATE_SUB_HEADERS)
            + count(self::HARI_KERJA_HEADERS)
            + count(self::TOTALS_HEADERS)
            + count(self::PAYROLL_HEADERS);

        $lastCol = $this->lastColumnLetter();
        $departmentName = $this->resolveDepartmentName();
        $periodLabel = $this->resolvePeriodLabel();

        $rows = [
            1 => 'PT. DELTA FORCE INDONESIA',
            3 => 'PT. CHAROEN POKPHAND INDONESIA - FOOD DIVISION',
            4 => 'DEPARTEMENT : ' . $departmentName,
            6 => 'REKAPITULASI ABSENSI, SHIFT DAN LEMBUR',
            7 => 'PERIODE : ' . $periodLabel,
        ];

        foreach ($rows as $row => $text) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", $text);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A6')->getFont()->setSize(12);
    }

    protected function buildTableHeader(Worksheet $sheet): void
    {
        $groupRow = 9;
        $labelRow = 10;
        $col = 1; // index kolom mulai dari A = 1
        $this->numericColumns = [];

        // --- Identitas (A-F) ---
        foreach (self::IDENTITY_HEADERS as $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->mergeCells("{$letter}{$groupRow}:{$letter}{$labelRow}");
            $sheet->setCellValue("{$letter}{$groupRow}", $label);
            $col++;
        }

        // --- Blok per tanggal ---
        foreach ($this->dates as $date) {
            $startLetter = Coordinate::stringFromColumnIndex($col);
            $endLetter = Coordinate::stringFromColumnIndex($col + count(self::DATE_SUB_HEADERS) - 1);

            $sheet->mergeCells("{$startLetter}{$groupRow}:{$endLetter}{$groupRow}");
            $sheet->setCellValue(
                "{$startLetter}{$groupRow}",
                $date->translatedFormat('l') . "\n" . $date->format('d/m/y')
            );
            $sheet->getStyle("{$startLetter}{$groupRow}")->getAlignment()->setWrapText(true);

            foreach (self::DATE_SUB_HEADERS as $subLabel) {
                $letter = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$letter}{$labelRow}", $subLabel);
                if (in_array($subLabel, ['JK', 'OT', 'CON'], true)) {
                    $this->numericColumns[] = $letter;
                }
                $col++;
            }
        }

        // --- Blok "HARI KERJA" (S, IZ, A, I, II, III) ---
        $startLetter = Coordinate::stringFromColumnIndex($col);
        $endLetter = Coordinate::stringFromColumnIndex($col + count(self::HARI_KERJA_HEADERS) - 1);
        $sheet->mergeCells("{$startLetter}{$groupRow}:{$endLetter}{$groupRow}");
        $sheet->setCellValue("{$startLetter}{$groupRow}", 'HARI KERJA');

        foreach (self::HARI_KERJA_HEADERS as $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$letter}{$labelRow}", $label);
            $this->numericColumns[] = $letter;
            $col++;
        }

        // --- JK 7 JAM / JK 5 JAM / TOTAL JK / TOTAL HK (masing-masing 1 kolom) ---
        foreach (self::TOTALS_HEADERS as $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->mergeCells("{$letter}{$groupRow}:{$letter}{$labelRow}");
            $sheet->setCellValue("{$letter}{$groupRow}", $label);
            $sheet->getStyle("{$letter}{$groupRow}")->getAlignment()->setWrapText(true);
            $this->numericColumns[] = $letter;
            $col++;
        }

        // --- Kolom payroll (masing-masing 1 kolom) ---
        foreach (self::PAYROLL_HEADERS as $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->mergeCells("{$letter}{$groupRow}:{$letter}{$labelRow}");
            $sheet->setCellValue("{$letter}{$groupRow}", $label);
            $sheet->getStyle("{$letter}{$groupRow}")->getAlignment()->setWrapText(true);
            $this->numericColumns[] = $letter;
            $col++;
        }

        $lastCol = $this->lastColumnLetter();
        $headerRange = "A{$groupRow}:{$lastCol}{$labelRow}";

        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle("A{$groupRow}:{$lastCol}{$groupRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');

        $sheet->getRowDimension($groupRow)->setRowHeight(30);
        $sheet->getRowDimension($labelRow)->setRowHeight(18);
    }

    protected function buildGrandTotalRow(
        Worksheet $sheet,
        int $firstDataRow,
        int $lastDataRow,
        int $grandTotalRow
    ): void {
        $lastCol = $this->lastColumnLetter();

        $sheet->mergeCells("A{$grandTotalRow}:F{$grandTotalRow}");
        $sheet->setCellValue("A{$grandTotalRow}", 'GRAND TOTAL');

        foreach ($this->numericColumns as $letter) {
            $sheet->setCellValue(
                "{$letter}{$grandTotalRow}",
                "=SUM({$letter}{$firstDataRow}:{$letter}{$lastDataRow})"
            );
        }

        $sheet->getStyle("A{$grandTotalRow}:{$lastCol}{$grandTotalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$grandTotalRow}:F{$grandTotalRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($grandTotalRow)->setRowHeight(25);
    }

    protected function applyGeneralStyles(
        Worksheet $sheet,
        int $firstDataRow,
        int $lastDataRow,
        int $grandTotalRow
    ): void {
        $lastCol = $this->lastColumnLetter();
        $fullRange = "A9:{$lastCol}{$grandTotalRow}";

        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($fullRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        if ($lastDataRow >= $firstDataRow) {
            $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach ($this->numericColumns as $letter) {
                $sheet->getStyle("{$letter}{$firstDataRow}:{$letter}{$lastDataRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.##');
                $sheet->getStyle("{$letter}{$firstDataRow}:{$letter}{$lastDataRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
    }

    protected function autoSizeColumns(Worksheet $sheet): void
    {
        // Kolom tanggal cukup sempit, sisanya lebih lebar. Set manual biar
        // tidak lambat (autosize bawaan PhpSpreadsheet mahal untuk kolom banyak).
        $col = 1;
        foreach (self::IDENTITY_HEADERS as $i => $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($letter)->setWidth([5, 16, 12, 22, 18, 18][$i] ?? 15);
            $col++;
        }
        $dateColCount = count($this->dates) * count(self::DATE_SUB_HEADERS);
        for ($i = 0; $i < $dateColCount; $i++) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($letter)->setWidth(7);
            $col++;
        }
        $restCount = count(self::HARI_KERJA_HEADERS) + count(self::TOTALS_HEADERS) + count(self::PAYROLL_HEADERS);
        for ($i = 0; $i < $restCount; $i++) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($letter)->setWidth(14);
            $col++;
        }
    }

    protected function resolveDepartmentName(): string
    {
        if (! $this->departmentId) {
            return 'SEMUA DEPARTEMENT';
        }

        $department = \App\Models\Department::find($this->departmentId);

        return $department->name ?? 'SEMUA DEPARTEMENT';
    }

    protected function resolvePeriodLabel(): string
    {
        $start = $this->dates[0];
        $end = end($this->dates);

        return $start->translatedFormat('d F Y') . ' S.D ' . $end->translatedFormat('d F Y');
    }
}