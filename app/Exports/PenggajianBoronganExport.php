<?php

namespace App\Exports;

use App\Models\CostCenter;
use App\Models\DailyActivityDetail;
use App\Models\DailyActivityDetailSlaughterHouse;
use App\Models\Department;
use App\Models\PenggajianBorongan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
    protected $outsourcingId;
    protected $costCenterId;
    protected int $no = 0;
    protected $costCenters;
    protected array $costCenterUpah = [];

    protected array $dates = [];

    protected array $dailyUpah = [];

    protected ?string $departmentName = null;
    protected ?string $outsourcingName = null;

    protected bool $showDateColumns = true;
    protected bool $showCostCenterColumns = true;

    protected const TITLE_ROWS = 8;

    public function __construct(
        int $month,
        int $year,
        ?int $departmentId = null,
        $outsourcingId = null,
        $costCenterId = null
    ) {
        $this->month = $month;
        $this->year = $year;
        $this->departmentId = $departmentId;
        $this->outsourcingId = $outsourcingId;
        $this->costCenterId = $costCenterId;

        if ($this->departmentId) {
            $this->costCenters = CostCenter::where(
                'department_id',
                $this->departmentId
            )
                ->orderBy('name')
                ->get();

            $this->departmentName = Department::find($this->departmentId)->name ?? null;

            $normalizedName = strtolower(trim($this->departmentName ?? ''));

            if ($normalizedName === 'sausage') {
                $this->showDateColumns = true;
                $this->showCostCenterColumns = false;
            } elseif ($normalizedName === 'slaughter house') {
                $this->showDateColumns = false;
                $this->showCostCenterColumns = true;
            }
        } else {
            $this->costCenters = CostCenter::orderBy('name')->get();
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $this->dates = iterator_to_array(
            CarbonPeriod::create($start, $end)
        );
    }

    public function collection()
    {
        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->whereHas('employee', function ($q) {
                $q->where('employee_status', 'borongan');
            });

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where(
                    'department_id',
                    $this->departmentId
                );
            });
        }

        if ($this->outsourcingId) {
            $query->whereHas('employee', function ($q) {
                $q->where(
                    'outsourcing_id',
                    $this->outsourcingId
                );
            });
        }

        if ($this->costCenterId) {
            $query->whereHas('employee', function ($q) {
                $q->where(
                    'cost_center_id',
                    $this->costCenterId
                );
            });
        }

        $payrolls = $query
            ->orderBy('employee_id')
            ->get();

        $employeeIds = $payrolls
            ->pluck('employee_id')
            ->unique()
            ->values();

        if ($this->outsourcingId) {
            $this->outsourcingName = \App\Models\Outsourcing::find($this->outsourcingId)->name ?? null;
        } else {
            $this->outsourcingName = $payrolls->first()->employee->outsourcing->name ?? null;
        }

        $this->costCenterUpah = [];
        $this->dailyUpah = [];

        $accumulateUpah = function ($row) {
            if (!isset($this->costCenterUpah[$row->employee_id][$row->cost_center_id])) {
                $this->costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
            }
            $this->costCenterUpah[$row->employee_id][$row->cost_center_id] += (float) $row->total_upah;

            $dateKey = Carbon::parse($row->tanggal)->format('Y-m-d');

            if (!isset($this->dailyUpah[$row->employee_id][$dateKey])) {
                $this->dailyUpah[$row->employee_id][$dateKey] = 0;
            }
            $this->dailyUpah[$row->employee_id][$dateKey] += (float) $row->total_upah;
        };

        if ($employeeIds->isNotEmpty()) {

            $sausageEmployeeIds = $payrolls
                ->filter(function ($payroll) {
                    return strtolower(
                        trim(
                            $payroll->employee->department->name ?? ''
                        )
                    ) === 'sausage';
                })
                ->pluck('employee_id')
                ->unique()
                ->values();

            $slaughterHouseEmployeeIds = $payrolls
                ->filter(function ($payroll) {
                    return strtolower(
                        trim(
                            $payroll->employee->department->name ?? ''
                        )
                    ) === 'slaughter house';
                })
                ->pluck('employee_id')
                ->unique()
                ->values();

            if ($sausageEmployeeIds->isNotEmpty()) {

                $sausageUpah = DailyActivityDetail::query()
                    ->join(
                        'daily_activities',
                        'daily_activities.id',
                        '=',
                        'daily_activity_details.daily_activity_id'
                    )
                    ->whereIn(
                        'daily_activities.employee_id',
                        $sausageEmployeeIds
                    )
                    ->whereMonth(
                        'daily_activities.tanggal',
                        $this->month
                    )
                    ->whereYear(
                        'daily_activities.tanggal',
                        $this->year
                    )
                    ->selectRaw('
                        daily_activities.employee_id,
                        daily_activities.cost_center_id,
                        daily_activities.tanggal,
                        SUM(daily_activity_details.total_harga) as total_upah
                    ')
                    ->groupBy(
                        'daily_activities.employee_id',
                        'daily_activities.cost_center_id',
                        'daily_activities.tanggal'
                    )
                    ->get();

                foreach ($sausageUpah as $row) {
                    $accumulateUpah($row);
                }
            }

            if ($slaughterHouseEmployeeIds->isNotEmpty()) {

                $slaughterHouseUpah =
                    DailyActivityDetailSlaughterHouse::query()
                    ->join(
                        'daily_activity_slaughter_houses',
                        'daily_activity_slaughter_houses.id',
                        '=',
                        'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                    )
                    ->whereIn(
                        'daily_activity_slaughter_houses.employee_id',
                        $slaughterHouseEmployeeIds
                    )
                    ->whereMonth(
                        'daily_activity_slaughter_houses.tanggal',
                        $this->month
                    )
                    ->whereYear(
                        'daily_activity_slaughter_houses.tanggal',
                        $this->year
                    )
                    ->selectRaw('
                        daily_activity_slaughter_houses.employee_id,
                        daily_activity_slaughter_houses.cost_center_id,
                        daily_activity_slaughter_houses.tanggal,
                        SUM(
                            daily_activity_detail_slaughter_houses.total_harga
                        ) as total_upah
                    ')
                    ->groupBy(
                        'daily_activity_slaughter_houses.employee_id',
                        'daily_activity_slaughter_houses.cost_center_id',
                        'daily_activity_slaughter_houses.tanggal'
                    )
                    ->get();

                foreach ($slaughterHouseUpah as $row) {
                    $accumulateUpah($row);
                }
            }
        }

        return $payrolls;
    }

    public function headings(): array
    {
        $row1 = [
            'NO',
            'NO. KTP',
            'NIK',
            'NAMA',
            'DEPARTMENT',
            'HASIL PROSES (Kg)/Jam',
            'TOTAL HARI',
        ];
        $row2 = ['', '', '', '', '', '', ''];

        if ($this->showDateColumns) {
            foreach ($this->dates as $date) {
                $row1[] = '';
                $row2[] = $date->translatedFormat('D') . "\n" . $date->format('d/m');
            }
        }

        if ($this->showCostCenterColumns) {
            foreach ($this->costCenters as $costCenter) {
                $row1[] = '';
                $row2[] = $costCenter->code . "\n" . $costCenter->name;
            }
        }

        $row1[] = 'TOTAL UPAH YANG DITERIMA';
        $row1[] = 'JAMSOSTEK (4.89%)';
        $row1[] = 'BPJS KESEHATAN (4%)';
        $row1[] = 'BPJS PENSIUN (2%)';
        $row1[] = 'MANAGEMEN FEE (175000/25)';
        $row1[] = 'GRAND TOTAL UPAH DITERIMA';

        $row2[] = '';
        $row2[] = '';
        $row2[] = '';
        $row2[] = '';
        $row2[] = '';
        $row2[] = '';

        return [
            $row1,
            $row2,
        ];
    }

    public function map($payroll): array
    {
        $this->no++;

        $row = [
            $this->no,
            $payroll->employee->ktp_number ?? '-',
            $payroll->employee->nik ?? '-',
            $payroll->employee->name ?? '-',
            $payroll->employee->department->name ?? '-',
            (float) ($payroll->total_kg ?? 0),
            (int) ($payroll->total_hari_kerja ?? 0),
        ];

        if ($this->showDateColumns) {
            foreach ($this->dates as $date) {
                $dateKey = $date->format('Y-m-d');
                $row[] = (float) ($this->dailyUpah[$payroll->employee_id][$dateKey] ?? 0);
            }
        }

        if ($this->showCostCenterColumns) {
            foreach ($this->costCenters as $costCenter) {

                $upahCostCenter =
                    $this->costCenterUpah[
                        $payroll->employee_id
                    ][
                        $costCenter->id
                    ] ?? 0;

                $row[] = (float) $upahCostCenter;
            }
        }

        $row[] = (float) ($payroll->total_upah ?? 0);
        $row[] = (float) ($payroll->jamsostek ?? 0);
        $row[] = (float) ($payroll->bpjs_kesehatan ?? 0);
        $row[] = (float) ($payroll->bpjs_pensiun ?? 0);
        $row[] = (float) ($payroll->managemen_fee ?? 0);
        $row[] = (float) ($payroll->grand_total_upah ?? 0);

        return $row;
    }

    public function columnWidths(): array
    {
        // Closure lokal konversi index kolom (1,2,3...) ke huruf (A,B,C...AA...)
        $getColumnLetter = function (int $column): string {
            $letter = '';
            while ($column > 0) {
                $modulo = ($column - 1) % 26;
                $letter = chr(65 + $modulo) . $letter;
                $column = (int) (($column - $modulo) / 26);
            }
            return $letter;
        };

        $widths = [
            'A' => 5,
            'B' => 18,
            'C' => 14,
            'D' => 25,
            'E' => 22,
            'F' => 20,
            'G' => 12,
        ];

        $dateStartColumn = 8;
        $dateCount = $this->showDateColumns ? count($this->dates) : 0;

        if ($this->showDateColumns) {
            foreach ($this->dates as $index => $date) {
                $column = $getColumnLetter($dateStartColumn + $index);
                $widths[$column] = 10;
            }
        }

        $costCenterStartColumn = $dateStartColumn + $dateCount;
        $costCenterCount = $this->showCostCenterColumns ? $this->costCenters->count() : 0;

        if ($this->showCostCenterColumns) {
            foreach ($this->costCenters as $index => $costCenter) {
                $column = $getColumnLetter($costCenterStartColumn + $index);
                $widths[$column] = 22;
            }
        }

        $afterCostCenter = $costCenterStartColumn + $costCenterCount;

        $widths[$getColumnLetter($afterCostCenter)] = 25;
        $widths[$getColumnLetter($afterCostCenter + 1)] = 20;
        $widths[$getColumnLetter($afterCostCenter + 2)] = 22;
        $widths[$getColumnLetter($afterCostCenter + 3)] = 20;
        $widths[$getColumnLetter($afterCostCenter + 4)] = 28;
        $widths[$getColumnLetter($afterCostCenter + 5)] = 25;

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

            2 => [
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

                $getColumnLetter = function (int $column): string {
                    $letter = '';
                    while ($column > 0) {
                        $modulo = ($column - 1) % 26;
                        $letter = chr(65 + $modulo) . $letter;
                        $column = (int) (($column - $modulo) / 26);
                    }
                    return $letter;
                };

                $originalLastRow = $sheet->getHighestRow();

                $dateStartColumn = 8;
                $dateCount = $this->showDateColumns ? count($this->dates) : 0;
                $dateEndColumn = $dateStartColumn + $dateCount - 1;

                $startCostCenterColumn = $dateStartColumn + $dateCount;
                $costCenterCount = $this->showCostCenterColumns ? $this->costCenters->count() : 0;
                $endCostCenterColumn = $startCostCenterColumn + $costCenterCount - 1;

                $totalUpahColumn = $startCostCenterColumn + $costCenterCount;
                $grandTotalColumn = $totalUpahColumn + 5;

                $dateStartLetter = $getColumnLetter($dateStartColumn);
                $dateEndLetter = $getColumnLetter($dateEndColumn);

                $startCostCenterLetter = $getColumnLetter($startCostCenterColumn);
                $endCostCenterLetter = $getColumnLetter($endCostCenterColumn);

                $totalUpahLetter = $getColumnLetter($totalUpahColumn);
                $grandTotalLetter = $getColumnLetter($grandTotalColumn);

                $sheet->insertNewRowBefore(1, self::TITLE_ROWS);

                $headerRow1 = self::TITLE_ROWS + 1;
                $headerRow2 = self::TITLE_ROWS + 2;
                $dataStartRow = self::TITLE_ROWS + 3;
                $lastDataRow = $originalLastRow + self::TITLE_ROWS;
                $grandTotalRow = $lastDataRow + 1;

                $lastColumn = $sheet->getHighestColumn();

                $departmentLabel = $this->departmentName ?? 'SEMUA DEPARTEMENT';

                $periodStart = $this->dates[0];
                $periodEnd = end($this->dates);
                $periodLabel = $periodStart->translatedFormat('d F Y') . ' S.D ' . $periodEnd->translatedFormat('d F Y');

                $titleRows = [
                    1 => $this->outsourcingName ?? 'PT. DELTA FORCE INDONESIA',
                    3 => 'PT. CHAROEN POKPHAND INDONESIA - FOOD DIVISION',
                    4 => 'DEPARTEMENT : ' . $departmentLabel,
                    6 => 'REKAPITULASI PENGGAJIAN BORONGAN',
                    7 => 'PERIODE : ' . $periodLabel,
                ];

                foreach ($titleRows as $row => $text) {
                    $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
                    $sheet->setCellValue("A{$row}", $text);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->getStyle('A1')->getFont()->setSize(14);
                $sheet->getStyle('A6')->getFont()->setSize(12);

                // --- Merge header blok tanggal (hanya kalau showDateColumns) ---
                if ($dateCount > 0) {
                    $sheet->mergeCells("{$dateStartLetter}{$headerRow1}:{$dateEndLetter}{$headerRow1}");
                    $sheet->setCellValue("{$dateStartLetter}{$headerRow1}", 'UPAH HARIAN (Rp)');
                }

                // --- Merge header blok cost center (hanya kalau showCostCenterColumns) ---
                if ($costCenterCount > 0) {

                    $sheet->mergeCells(
                        "{$startCostCenterLetter}{$headerRow1}:{$endCostCenterLetter}{$headerRow1}"
                    );
                }

                $fixedColumns = [
                    'A',
                    'B',
                    'C',
                    'D',
                    'E',
                    'F',
                    'G',
                    $totalUpahLetter,
                    $getColumnLetter($totalUpahColumn + 1),
                    $getColumnLetter($totalUpahColumn + 2),
                    $getColumnLetter($totalUpahColumn + 3),
                    $getColumnLetter($totalUpahColumn + 4),
                    $grandTotalLetter,
                ];

                foreach ($fixedColumns as $column) {

                    $sheet->mergeCells(
                        "{$column}{$headerRow1}:{$column}{$headerRow2}"
                    );
                }

                $sheet
                    ->getStyle(
                        "A{$headerRow1}:{$lastColumn}{$headerRow2}"
                    )
                    ->getFont()
                    ->setBold(true);

                $sheet
                    ->getStyle(
                        "A{$headerRow1}:{$lastColumn}{$headerRow2}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "A{$headerRow1}:{$lastColumn}{$headerRow2}"
                    )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "A{$headerRow1}:{$lastColumn}{$headerRow2}"
                    )
                    ->getAlignment()
                    ->setWrapText(true);

                $sheet
                    ->getStyle(
                        "A{$headerRow1}:{$lastColumn}{$lastDataRow}"
                    )
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );

                $sheet
                    ->getStyle(
                        "A{$headerRow1}:{$lastColumn}{$lastDataRow}"
                    )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getRowDimension($headerRow1)
                    ->setRowHeight(25);

                $sheet
                    ->getRowDimension($headerRow2)
                    ->setRowHeight(40);

                $sheet
                    ->getStyle(
                        "F{$dataStartRow}:F{$lastDataRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '#,##0.00'
                    );

                if ($dateCount > 0) {
                    $sheet
                        ->getStyle(
                            "{$dateStartLetter}{$dataStartRow}:{$dateEndLetter}{$lastDataRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0'
                        );
                }

                if ($costCenterCount > 0) {

                    $sheet
                        ->getStyle(
                            "{$startCostCenterLetter}{$dataStartRow}:{$endCostCenterLetter}{$lastDataRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0'
                        );
                }

                $sheet
                    ->getStyle(
                        "{$totalUpahLetter}{$dataStartRow}:{$grandTotalLetter}{$lastDataRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '#,##0'
                    );

                $sheet
                    ->getStyle(
                        "A{$dataStartRow}:A{$lastDataRow}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "F{$dataStartRow}:G{$lastDataRow}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                 * GRAND TOTAL
                 */
                $sheet->setCellValue(
                    "A{$grandTotalRow}",
                    'GRAND TOTAL'
                );

                $sheet->mergeCells(
                    "A{$grandTotalRow}:E{$grandTotalRow}"
                );

                $sheet->setCellValue(
                    "F{$grandTotalRow}",
                    "=SUM(F{$dataStartRow}:F{$lastDataRow})"
                );

                $sheet->setCellValue(
                    "G{$grandTotalRow}",
                    "=SUM(G{$dataStartRow}:G{$lastDataRow})"
                );

                if ($dateCount > 0) {
                    for ($i = 0; $i < $dateCount; $i++) {
                        $column = $getColumnLetter($dateStartColumn + $i);

                        $sheet->setCellValue(
                            "{$column}{$grandTotalRow}",
                            "=SUM({$column}{$dataStartRow}:{$column}{$lastDataRow})"
                        );
                    }
                }

                if ($costCenterCount > 0) {

                    for (
                        $i = 0;
                        $i < $costCenterCount;
                        $i++
                    ) {

                        $column = $getColumnLetter($startCostCenterColumn + $i);

                        $sheet->setCellValue(
                            "{$column}{$grandTotalRow}",
                            "=SUM({$column}{$dataStartRow}:{$column}{$lastDataRow})"
                        );
                    }
                }

                $sheet->setCellValue(
                    "{$totalUpahLetter}{$grandTotalRow}",
                    "=SUM({$totalUpahLetter}{$dataStartRow}:{$totalUpahLetter}{$lastDataRow})"
                );

                $sheet->setCellValue(
                    $getColumnLetter($totalUpahColumn + 1) . $grandTotalRow,
                    "=SUM(" .
                    $getColumnLetter($totalUpahColumn + 1) .
                    "{$dataStartRow}:" .
                    $getColumnLetter($totalUpahColumn + 1) .
                    "{$lastDataRow})"
                );

                $sheet->setCellValue(
                    $getColumnLetter($totalUpahColumn + 2) . $grandTotalRow,
                    "=SUM(" .
                    $getColumnLetter($totalUpahColumn + 2) .
                    "{$dataStartRow}:" .
                    $getColumnLetter($totalUpahColumn + 2) .
                    "{$lastDataRow})"
                );

                $sheet->setCellValue(
                    $getColumnLetter($totalUpahColumn + 3) . $grandTotalRow,
                    "=SUM(" .
                    $getColumnLetter($totalUpahColumn + 3) .
                    "{$dataStartRow}:" .
                    $getColumnLetter($totalUpahColumn + 3) .
                    "{$lastDataRow})"
                );

                $sheet->setCellValue(
                    $getColumnLetter($totalUpahColumn + 4) . $grandTotalRow,
                    "=SUM(" .
                    $getColumnLetter($totalUpahColumn + 4) .
                    "{$dataStartRow}:" .
                    $getColumnLetter($totalUpahColumn + 4) .
                    "{$lastDataRow})"
                );

                $sheet->setCellValue(
                    $grandTotalLetter . $grandTotalRow,
                    "=SUM({$grandTotalLetter}{$dataStartRow}:{$grandTotalLetter}{$lastDataRow})"
                );

                $sheet
                    ->getStyle(
                        "A{$grandTotalRow}:{$lastColumn}{$grandTotalRow}"
                    )
                    ->getFont()
                    ->setBold(true);

                $sheet
                    ->getStyle(
                        "A{$grandTotalRow}:{$lastColumn}{$grandTotalRow}"
                    )
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );

                $sheet
                    ->getStyle(
                        "A{$grandTotalRow}:{$lastColumn}{$grandTotalRow}"
                    )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "A{$grandTotalRow}:{$lastColumn}{$grandTotalRow}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "F{$grandTotalRow}:{$grandTotalLetter}{$grandTotalRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '#,##0.00'
                    );

                if ($dateCount > 0) {
                    $sheet
                        ->getStyle(
                            "{$dateStartLetter}{$grandTotalRow}:{$dateEndLetter}{$grandTotalRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0'
                        );
                }

                if ($costCenterCount > 0) {

                    $sheet
                        ->getStyle(
                            "{$startCostCenterLetter}{$grandTotalRow}:{$endCostCenterLetter}{$grandTotalRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0'
                        );
                }

                $sheet
                    ->getStyle(
                        "{$totalUpahLetter}{$grandTotalRow}:{$grandTotalLetter}{$grandTotalRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '#,##0'
                    );

                $sheet
                    ->getRowDimension($grandTotalRow)
                    ->setRowHeight(25);
            },
        ];
    }
}