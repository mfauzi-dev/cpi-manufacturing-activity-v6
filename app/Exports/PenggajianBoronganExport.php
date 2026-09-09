<?php

namespace App\Exports;

use App\Models\CostCenter;
use App\Models\DailyActivityDetail;
use App\Models\DailyActivityDetailSlaughterHouse;
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

    /** @var Carbon[] semua tanggal dalam periode, dipakai untuk kolom per-tanggal */
    protected array $dates = [];

    /** @var array<int, array<string, float>> [employee_id][Y-m-d] => total upah hari itu */
    protected array $dailyUpah = [];

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

        $this->costCenterUpah = [];
        $this->dailyUpah = [];

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

            // Catatan: query di bawah sekarang ikut group by tanggal (bukan
            // cuma employee_id + cost_center_id) supaya dari satu query yang
            // sama kita bisa mengisi dua rekap sekaligus:
            // 1) total upah per cost center (sepanjang bulan)  -> costCenterUpah
            // 2) total upah per tanggal (semua cost center digabung) -> dailyUpah

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
                    $this->accumulateUpah($row);
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
                    $this->accumulateUpah($row);
                }
            }
        }

        return $payrolls;
    }

    /**
     * Tambahkan satu baris hasil query (employee_id, cost_center_id,
     * tanggal, total_upah) ke dua rekap: costCenterUpah dan dailyUpah.
     */
    protected function accumulateUpah($row): void
    {
        if (!isset($this->costCenterUpah[$row->employee_id][$row->cost_center_id])) {
            $this->costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
        }
        $this->costCenterUpah[$row->employee_id][$row->cost_center_id] += (float) $row->total_upah;

        $dateKey = Carbon::parse($row->tanggal)->format('Y-m-d');

        if (!isset($this->dailyUpah[$row->employee_id][$dateKey])) {
            $this->dailyUpah[$row->employee_id][$dateKey] = 0;
        }
        $this->dailyUpah[$row->employee_id][$dateKey] += (float) $row->total_upah;
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

        // Blok per tanggal: row1 nanti di-merge jadi satu judul "UPAH HARIAN"
        // di registerEvents(), di sini cukup kosongkan dan isi tanggal di row2.
        foreach ($this->dates as $date) {
            $row1[] = '';
            $row2[] = $date->translatedFormat('D') . "\n" . $date->format('d/m');
        }

        // Blok cost center (sama seperti sebelumnya)
        foreach ($this->costCenters as $costCenter) {
            $row1[] = '';
            $row2[] = $costCenter->code . "\n" . $costCenter->name;
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

        foreach ($this->dates as $date) {
            $dateKey = $date->format('Y-m-d');
            $row[] = (float) ($this->dailyUpah[$payroll->employee_id][$dateKey] ?? 0);
        }

        foreach ($this->costCenters as $costCenter) {

            $upahCostCenter =
                $this->costCenterUpah[
                    $payroll->employee_id
                ][
                    $costCenter->id
                ] ?? 0;

            $row[] = (float) $upahCostCenter;
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

        foreach ($this->dates as $index => $date) {
            $column = $this->getColumnLetter($dateStartColumn + $index);
            $widths[$column] = 10;
        }

        $costCenterStartColumn = $dateStartColumn + count($this->dates);

        foreach ($this->costCenters as $index => $costCenter) {

            $column = $this->getColumnLetter(
                $costCenterStartColumn + $index
            );

            $widths[$column] = 22;
        }

        $afterCostCenter =
            $costCenterStartColumn +
            $this->costCenters->count();

        $widths[
            $this->getColumnLetter($afterCostCenter)
        ] = 25;

        $widths[
            $this->getColumnLetter($afterCostCenter + 1)
        ] = 20;

        $widths[
            $this->getColumnLetter($afterCostCenter + 2)
        ] = 22;

        $widths[
            $this->getColumnLetter($afterCostCenter + 3)
        ] = 20;

        $widths[
            $this->getColumnLetter($afterCostCenter + 4)
        ] = 28;

        $widths[
            $this->getColumnLetter($afterCostCenter + 5)
        ] = 25;

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

                $lastDataRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                // --- Posisi kolom, dihitung dinamis ---
                $dateStartColumn = 8;
                $dateCount = count($this->dates);
                $dateEndColumn = $dateStartColumn + $dateCount - 1;

                $startCostCenterColumn = $dateStartColumn + $dateCount;
                $costCenterCount = $this->costCenters->count();
                $endCostCenterColumn = $startCostCenterColumn + $costCenterCount - 1;

                $totalUpahColumn = $startCostCenterColumn + $costCenterCount;
                $grandTotalColumn = $totalUpahColumn + 5;

                $dateStartLetter = $this->getColumnLetter($dateStartColumn);
                $dateEndLetter = $this->getColumnLetter($dateEndColumn);

                $startCostCenterLetter = $this->getColumnLetter($startCostCenterColumn);
                $endCostCenterLetter = $this->getColumnLetter($endCostCenterColumn);

                $totalUpahLetter = $this->getColumnLetter($totalUpahColumn);
                $grandTotalLetter = $this->getColumnLetter($grandTotalColumn);

                // --- Merge header blok tanggal (row1) ---
                if ($dateCount > 0) {
                    $sheet->mergeCells("{$dateStartLetter}1:{$dateEndLetter}1");
                    $sheet->setCellValue("{$dateStartLetter}1", 'UPAH HARIAN (Rp)');
                }

                // --- Merge header blok cost center (row1) ---
                if ($costCenterCount > 0) {

                    $sheet->mergeCells(
                        "{$startCostCenterLetter}1:{$endCostCenterLetter}1"
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
                    $this->getColumnLetter(
                        $totalUpahColumn + 1
                    ),
                    $this->getColumnLetter(
                        $totalUpahColumn + 2
                    ),
                    $this->getColumnLetter(
                        $totalUpahColumn + 3
                    ),
                    $this->getColumnLetter(
                        $totalUpahColumn + 4
                    ),
                    $grandTotalLetter,
                ];

                foreach ($fixedColumns as $column) {

                    $sheet->mergeCells(
                        "{$column}1:{$column}2"
                    );
                }

                $sheet
                    ->getStyle(
                        "A1:{$lastColumn}2"
                    )
                    ->getFont()
                    ->setBold(true);

                $sheet
                    ->getStyle(
                        "A1:{$lastColumn}2"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "A1:{$lastColumn}2"
                    )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "A1:{$lastColumn}2"
                    )
                    ->getAlignment()
                    ->setWrapText(true);

                $sheet
                    ->getStyle(
                        "A1:{$lastColumn}{$lastDataRow}"
                    )
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );

                $sheet
                    ->getStyle(
                        "A1:{$lastColumn}{$lastDataRow}"
                    )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getRowDimension(1)
                    ->setRowHeight(25);

                $sheet
                    ->getRowDimension(2)
                    ->setRowHeight(40);

                $sheet
                    ->getStyle(
                        "F3:F{$lastDataRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '#,##0.00'
                    );

                if ($dateCount > 0) {
                    $sheet
                        ->getStyle(
                            "{$dateStartLetter}3:{$dateEndLetter}{$lastDataRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0'
                        );
                }

                if ($costCenterCount > 0) {

                    $sheet
                        ->getStyle(
                            "{$startCostCenterLetter}3:{$endCostCenterLetter}{$lastDataRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0'
                        );
                }

                $sheet
                    ->getStyle(
                        "{$totalUpahLetter}3:{$grandTotalLetter}{$lastDataRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '#,##0'
                    );

                $sheet
                    ->getStyle(
                        "A3:A{$lastDataRow}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet
                    ->getStyle(
                        "F3:G{$lastDataRow}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                 * GRAND TOTAL
                 */
                $grandTotalRow = $lastDataRow + 1;

                $sheet->setCellValue(
                    "A{$grandTotalRow}",
                    'GRAND TOTAL'
                );

                /*
                 * Merge A sampai E
                 */
                $sheet->mergeCells(
                    "A{$grandTotalRow}:E{$grandTotalRow}"
                );

                /*
                 * Total Hasil Proses
                 */
                $sheet->setCellValue(
                    "F{$grandTotalRow}",
                    "=SUM(F3:F{$lastDataRow})"
                );

                /*
                 * Total Hari
                 */
                $sheet->setCellValue(
                    "G{$grandTotalRow}",
                    "=SUM(G3:G{$lastDataRow})"
                );

                /*
                 * Total upah per tanggal
                 */
                if ($dateCount > 0) {
                    for ($i = 0; $i < $dateCount; $i++) {
                        $column = $this->getColumnLetter($dateStartColumn + $i);

                        $sheet->setCellValue(
                            "{$column}{$grandTotalRow}",
                            "=SUM({$column}3:{$column}{$lastDataRow})"
                        );
                    }
                }

                /*
                 * Total masing-masing Cost Center
                 */
                if ($costCenterCount > 0) {

                    for (
                        $i = 0;
                        $i < $costCenterCount;
                        $i++
                    ) {

                        $column =
                            $this->getColumnLetter(
                                $startCostCenterColumn + $i
                            );

                        $sheet->setCellValue(
                            "{$column}{$grandTotalRow}",
                            "=SUM({$column}3:{$column}{$lastDataRow})"
                        );
                    }
                }

                /*
                 * Total Upah
                 */
                $sheet->setCellValue(
                    "{$totalUpahLetter}{$grandTotalRow}",
                    "=SUM({$totalUpahLetter}3:{$totalUpahLetter}{$lastDataRow})"
                );

                /*
                 * Jamsostek
                 */
                $sheet->setCellValue(
                    $this->getColumnLetter(
                        $totalUpahColumn + 1
                    ) . $grandTotalRow,
                    "=SUM(" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 1
                    ) .
                    "3:" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 1
                    ) .
                    "{$lastDataRow})"
                );

                /*
                 * BPJS Kesehatan
                 */
                $sheet->setCellValue(
                    $this->getColumnLetter(
                        $totalUpahColumn + 2
                    ) . $grandTotalRow,
                    "=SUM(" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 2
                    ) .
                    "3:" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 2
                    ) .
                    "{$lastDataRow})"
                );

                /*
                 * BPJS Pensiun
                 */
                $sheet->setCellValue(
                    $this->getColumnLetter(
                        $totalUpahColumn + 3
                    ) . $grandTotalRow,
                    "=SUM(" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 3
                    ) .
                    "3:" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 3
                    ) .
                    "{$lastDataRow})"
                );

                /*
                 * Management Fee
                 */
                $sheet->setCellValue(
                    $this->getColumnLetter(
                        $totalUpahColumn + 4
                    ) . $grandTotalRow,
                    "=SUM(" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 4
                    ) .
                    "3:" .
                    $this->getColumnLetter(
                        $totalUpahColumn + 4
                    ) .
                    "{$lastDataRow})"
                );

                /*
                 * Grand Total Upah
                 */
                $sheet->setCellValue(
                    $grandTotalLetter . $grandTotalRow,
                    "=SUM({$grandTotalLetter}3:{$grandTotalLetter}{$lastDataRow})"
                );

                /*
                 * Style GRAND TOTAL
                 */
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

    private function getColumnLetter(int $column): string
    {
        $letter = '';

        while ($column > 0) {

            $modulo = ($column - 1) % 26;

            $letter =
                chr(65 + $modulo) .
                $letter;

            $column =
                (int) (($column - $modulo) / 26);
        }

        return $letter;
    }
}