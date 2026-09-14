<?php

namespace App\Exports;

use App\Models\DailyActivityDetailFurther;
use App\Models\Line;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DailyActivityFurtherExport implements FromCollection, WithStyles, ShouldAutoSize, WithEvents
{
    protected $costCenterId;
    protected $psGroupId;
    protected $fromDate;
    protected $toDate;
    protected $departmentId;
    protected $lineId;

    protected $groupedDetails = [];
    protected $lines = [];

    // flattened list of (line + ps_group) sections, built in collection()
    // and re-used identically inside registerEvents() so both stay in sync.
    protected $sections = [];

    // overall grand total row(s) across ALL lines/groups, built in
    // collection() and re-used in registerEvents() for styling.
    // Each entry: ['label' => ..., 'rm' => ..., 'fg' => ..., 'kg' => ...]
    // NOTE: man_hours / productivity are intentionally NOT included here,
    // per requirement — the overall grand total only totals RM/FG/KG.
    protected $overallTotals = [];

    public function __construct(
        $costCenterId = null,
        $psGroupId = null,
        $fromDate = null,
        $toDate = null,
        $departmentId = null,
        $lineId = null
    ) {
        $this->costCenterId = $costCenterId;
        $this->psGroupId = $psGroupId;
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->toDate = $toDate ?? now()->format('Y-m-d');
        $this->departmentId = $departmentId;
        $this->lineId = $lineId;
    }

    // builds a section label like "LINE 1 - GROUP A" without
    // double-prefixing when the line name already contains "LINE".
    protected function buildSectionLabel($lineName, $psGroupName)
    {
        $lineLabel = trim($lineName);

        if (stripos($lineLabel, 'line') !== 0) {
            $lineLabel = 'LINE ' . $lineLabel;
        }

        if (!empty($psGroupName) && $psGroupName !== '-') {
            $lineLabel .= ' - ' . strtoupper($psGroupName);
        }

        return $lineLabel;
    }

    public function collection()
    {
        $query = DailyActivityDetailFurther::query()
            ->with([
                'product',
                'dailyActivityFurther.line',
                'dailyActivityFurther.psGroup',
            ])
            ->whereHas('dailyActivityFurther', function ($q) {

                if ($this->costCenterId) {
                    $q->where(
                        'cost_center_id',
                        $this->costCenterId
                    );
                }

                if ($this->psGroupId) {
                    $q->where(
                        'ps_group_id',
                        $this->psGroupId
                    );
                }

                if ($this->lineId) {
                    $q->where(
                        'line_id',
                        $this->lineId
                    );
                }

                if ($this->departmentId) {
                    $q->where(
                        'department_id',
                        $this->departmentId
                    );
                }

                if ($this->fromDate) {
                    $q->whereDate(
                        'tanggal',
                        '>=',
                        $this->fromDate
                    );
                }

                if ($this->toDate) {
                    $q->whereDate(
                        'tanggal',
                        '<=',
                        $this->toDate
                    );
                }
            })
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy(
                'daily_activity_furthers.line_id'
            )
            ->orderBy(
                'daily_activity_furthers.ps_group_id'
            )
            ->orderBy(
                'daily_activity_furthers.tanggal'
            )
            ->orderBy(
                'daily_activity_detail_furthers.id'
            )
            ->select(
                'daily_activity_detail_furthers.*'
            );

        $details = $query->get();

        // grouped[line_id][ps_group_id][tanggal] = [...]
        $grouped = [];

        // groupNames[line_id][ps_group_id] = 'Group A'
        $groupNames = [];

        foreach ($details as $detail) {

            $daf = $detail->dailyActivityFurther;

            if (!$daf) {
                continue;
            }

            $lineId = $daf->line_id;

            $psGroupId = $daf->ps_group_id ?? 0;

            $psGroupName = $daf->psGroup->name ?? '-';

            $tanggal = Carbon::parse(
                $daf->tanggal
            )->format('Y-m-d');

            if (!isset($grouped[$lineId])) {
                $grouped[$lineId] = [];
            }

            if (!isset($grouped[$lineId][$psGroupId])) {
                $grouped[$lineId][$psGroupId] = [];
            }

            $groupNames[$lineId][$psGroupId] = $psGroupName;

            if (!isset($grouped[$lineId][$psGroupId][$tanggal])) {

                $grouped[$lineId][$psGroupId][$tanggal] = [
                    'tanggal' => $daf->tanggal,
                    'line_id' => $lineId,
                    'line_name' => $daf->line->name ?? '-',
                    'ps_group_id' => $psGroupId,
                    'ps_group_name' => $psGroupName,
                    'employees' => [],
                    'products' => [],
                ];
            }

            if (!isset(
                $grouped[$lineId][$psGroupId][$tanggal]['employees'][$detail->employee_id]
            )) {

                $grouped[$lineId][$psGroupId][$tanggal]['employees'][$detail->employee_id] = [
                    'man_power' => (float) $detail->man_power,
                ];
            }

            $productKey = $detail->product_id;

            if (!isset(
                $grouped[$lineId][$psGroupId][$tanggal]['products'][$productKey]
            )) {

                $grouped[$lineId][$psGroupId][$tanggal]['products'][$productKey] = [
                    'product_id' => $detail->product_id,
                    'material_code' =>
                        $detail->product->material_code ?? '-',
                    'material_name' =>
                        $detail->product->material_name ?? '-',
                    'total_kg_rm' =>
                        (float) $detail->total_kg_rm,
                    'total_kg_fg' =>
                        (float) $detail->total_kg_fg,
                ];
            }
        }

        $this->groupedDetails = $grouped;

        if ($this->lineId) {

            $lineQuery = Line::query()
                ->where('id', $this->lineId);

            if ($this->departmentId) {
                $lineQuery->where(
                    'department_id',
                    $this->departmentId
                );
            }

            $this->lines = $lineQuery
                ->orderByRaw('CAST(name AS UNSIGNED)')
                ->orderBy('name')
                ->get();

        } elseif ($this->departmentId) {

            $this->lines = Line::query()
                ->where(
                    'department_id',
                    $this->departmentId
                )
                ->whereIn(
                    'id',
                    array_keys($grouped)
                )
                ->orderByRaw('CAST(name AS UNSIGNED)')
                ->orderBy('name')
                ->get();

        } else {

            $lineIds = array_keys($grouped);

            $this->lines = Line::query()
                ->whereIn('id', $lineIds)
                ->orderByRaw('CAST(name AS UNSIGNED)')
                ->orderBy('name')
                ->get();
        }

        // flatten (line, ps_group) into a single ordered list of "sections".
        $sections = [];

        foreach ($this->lines as $line) {

            $lineGroups = $grouped[$line->id] ?? [];

            if (count($lineGroups) === 0) {

                $sections[] = [
                    'line_id' => $line->id,
                    'line_name' => $line->name,
                    'ps_group_id' => null,
                    'ps_group_name' => null,
                    'dates' => [],
                ];

                continue;
            }

            foreach ($lineGroups as $psGroupId => $dates) {

                $sections[] = [
                    'line_id' => $line->id,
                    'line_name' => $line->name,
                    'ps_group_id' => $psGroupId,
                    'ps_group_name' => $groupNames[$line->id][$psGroupId] ?? '-',
                    'dates' => $dates,
                ];
            }
        }

        $this->sections = $sections;

        // accumulator for the overall (all lines/groups combined) totals,
        // keyed by raw 'Y-m-d' date so we can sort and, if the date filter
        // spans more than one day, emit one grand total row per date at
        // the very end of the report. Only RM/FG/KG are accumulated —
        // man hours & productivity are not part of the overall grand total.
        $overallByDate = [];

        $rows = [];

        foreach ($this->sections as $section) {

            $sectionLabel = $this->buildSectionLabel(
                $section['line_name'],
                $section['ps_group_name']
            );

            $rows[] = [
                '',
                $sectionLabel,
                '',
                '',
                '',
                '',
                '',
                '',
            ];

            $rows[] = [
                '',
                'Tanggal',
                'Product',
                'Production',
                '',
                'Total KG',
                'Man Hours',
                'Productivity',
            ];

            $rows[] = [
                '',
                '',
                '',
                'RM',
                'FG',
                '',
                '',
                '',
            ];

            $lineGroups = $section['dates'];

            if (count($lineGroups) === 0) {

                // NEW: only the "-" placeholder row remains for an empty
                // line/group — the old "GRAND TOTAL" row for empty lines
                // has been removed (no more per-line grand total at all).
                $rows[] = [
                    '',
                    '-',
                    '-',
                    0,
                    0,
                    0,
                    0,
                    0,
                ];

                $rows[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];

            } else {

                foreach ($lineGroups as $group) {

                    $manHours = 0;

                    foreach ($group['employees'] as $employeeData) {
                        $manHours += (float) $employeeData['man_power'];
                    }

                    $dateTotalRm = 0;
                    $dateTotalFg = 0;

                    foreach ($group['products'] as $product) {

                        $dateTotalRm +=
                            (float) $product['total_kg_rm'];

                        $dateTotalFg +=
                            (float) $product['total_kg_fg'];
                    }

                    $dateTotalKg = $dateTotalFg;

                    $dateProductivity = $manHours > 0
                        ? $dateTotalFg / $manHours
                        : 0;

                    $firstProduct = true;

                    foreach ($group['products'] as $product) {

                        $rows[] = [
                            '',
                            $firstProduct
                                ? Carbon::parse(
                                    $group['tanggal']
                                )->format('d M Y')
                                : '',
                            $product['material_name'] . "\n" .
                                ($product['material_code'] ?? '-'),
                            (float) $product['total_kg_rm'],
                            (float) $product['total_kg_fg'],
                            $firstProduct
                                ? $dateTotalKg
                                : '',
                            $firstProduct
                                ? $manHours
                                : '',
                            $firstProduct
                                ? $dateProductivity
                                : '',
                        ];

                        $firstProduct = false;
                    }

                    // RENAMED: was "GRAND TOTAL {date}" — now "SUB TOTAL
                    // {date}" since this is the per-date subtotal inside a
                    // single LINE/Group block, not an overall grand total.
                    $rows[] = [
                        '',
                        'SUB TOTAL ' .
                            strtoupper(
                                Carbon::parse(
                                    $group['tanggal']
                                )->format('d M Y')
                            ),
                        '',
                        $dateTotalRm,
                        $dateTotalFg,
                        $dateTotalKg,
                        $manHours,
                        $dateProductivity,
                    ];

                    // fold this date's totals into the cross-line
                    // accumulator, keyed by the raw Y-m-d date.
                    $dateKey = Carbon::parse($group['tanggal'])->format('Y-m-d');

                    if (!isset($overallByDate[$dateKey])) {
                        $overallByDate[$dateKey] = [
                            'rm' => 0,
                            'fg' => 0,
                            'kg' => 0,
                        ];
                    }

                    $overallByDate[$dateKey]['rm'] += $dateTotalRm;
                    $overallByDate[$dateKey]['fg'] += $dateTotalFg;
                    $overallByDate[$dateKey]['kg'] += $dateTotalKg;
                }

                // NOTE: the old "GRAND TOTAL LINE ..." row (summing this
                // whole section) has been removed entirely, per
                // requirement — sections now end right after the last
                // date's SUB TOTAL row.

                $rows[] = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];
            }
        }

        // build the overall grand total row(s), across ALL lines and
        // ps_groups combined. Only RM/FG/KG are totaled — Man Hours and
        // Productivity are left blank, per requirement.
        // - If the date filter is a single day (fromDate === toDate),
        //   emit exactly ONE "GRAND TOTAL" row.
        // - Otherwise (a date range), emit one "GRAND TOTAL <date>" row
        //   per date in the range, sorted chronologically.
        $overallTotals = [];

        if (count($overallByDate) > 0) {

            $isSingleDay = $this->fromDate === $this->toDate;

            if ($isSingleDay) {

                $grandRm = 0;
                $grandFg = 0;
                $grandKg = 0;

                foreach ($overallByDate as $d) {
                    $grandRm += $d['rm'];
                    $grandFg += $d['fg'];
                    $grandKg += $d['kg'];
                }

                $overallTotals[] = [
                    'label' => 'GRAND TOTAL',
                    'rm' => $grandRm,
                    'fg' => $grandFg,
                    'kg' => $grandKg,
                ];

            } else {

                ksort($overallByDate);

                foreach ($overallByDate as $dateKey => $d) {

                    $overallTotals[] = [
                        'label' => 'GRAND TOTAL ' .
                            strtoupper(
                                Carbon::parse($dateKey)->format('d M Y')
                            ),
                        'rm' => $d['rm'],
                        'fg' => $d['fg'],
                        'kg' => $d['kg'],
                    ];
                }
            }
        }

        $this->overallTotals = $overallTotals;

        foreach ($overallTotals as $total) {

            $rows[] = [
                '',
                $total['label'],
                '',
                $total['rm'],
                $total['fg'],
                $total['kg'],
                '',
                '',
            ];
        }

        return new Collection($rows);
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 4);

                $bulan = Carbon::parse(
                    $this->fromDate
                )->translatedFormat('F Y');

                $sheet->setCellValue(
                    'B3',
                    'LAPORAN HASIL PRODUKSI FURTHER ' .
                        strtoupper($bulan)
                );

                $sheet->mergeCells('B3:H3');

                $sheet->getStyle('B3:H3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' =>
                            Alignment::HORIZONTAL_CENTER,
                        'vertical' =>
                            Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension(3)->setRowHeight(32);

                $currentRow = 5;

                foreach ($this->sections as $section) {

                    $sectionLabel = $this->buildSectionLabel(
                        $section['line_name'],
                        $section['ps_group_name']
                    );

                    $lineRow = $currentRow;

                    $sheet->mergeCells(
                        'B' . $lineRow . ':H' . $lineRow
                    );

                    $sheet->setCellValue(
                        'B' . $lineRow,
                        $sectionLabel
                    );

                    $sheet->getStyle(
                        'B' . $lineRow . ':H' . $lineRow
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 14,
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_LEFT,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $sheet->getRowDimension(
                        $lineRow
                    )->setRowHeight(30);

                    $currentRow++;

                    $headerRow1 = $currentRow;
                    $headerRow2 = $currentRow + 1;

                    $sheet->mergeCells(
                        'B' . $headerRow1 . ':B' . $headerRow2
                    );

                    $sheet->mergeCells(
                        'C' . $headerRow1 . ':C' . $headerRow2
                    );

                    $sheet->mergeCells(
                        'D' . $headerRow1 . ':E' . $headerRow1
                    );

                    $sheet->mergeCells(
                        'F' . $headerRow1 . ':F' . $headerRow2
                    );

                    $sheet->mergeCells(
                        'G' . $headerRow1 . ':G' . $headerRow2
                    );

                    $sheet->mergeCells(
                        'H' . $headerRow1 . ':H' . $headerRow2
                    );

                    $sheet->getStyle(
                        'B' . $headerRow1 . ':H' . $headerRow2
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                    Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $sheet->getRowDimension(
                        $headerRow1
                    )->setRowHeight(28);

                    $sheet->getRowDimension(
                        $headerRow2
                    )->setRowHeight(28);

                    $currentRow += 2;

                    $lineGroups = $section['dates'];

                    $dataStartRow = $currentRow;

                    foreach ($lineGroups as $group) {

                        $productCount =
                            count($group['products']);

                        if ($productCount > 1) {

                            $endRow =
                                $currentRow +
                                $productCount -
                                1;

                            $sheet->mergeCells(
                                'B' . $currentRow . ':B' . $endRow
                            );

                            $sheet->mergeCells(
                                'F' . $currentRow . ':F' . $endRow
                            );

                            $sheet->mergeCells(
                                'G' . $currentRow . ':G' . $endRow
                            );

                            $sheet->mergeCells(
                                'H' . $currentRow . ':H' . $endRow
                            );
                        }

                        for (
                            $row = $currentRow;
                            $row <= $currentRow + $productCount - 1;
                            $row++
                        ) {
                            $sheet->getRowDimension(
                                $row
                            )->setRowHeight(42);
                        }

                        $currentRow += $productCount;

                        // this is now the "SUB TOTAL {date}" row.
                        $subTotalRow = $currentRow;

                        $sheet->getStyle(
                            'B' . $subTotalRow . ':H' . $subTotalRow
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        $sheet->getStyle(
                            'D' . $subTotalRow . ':H' . $subTotalRow
                        )->getNumberFormat()
                            ->setFormatCode('#,##0.00');

                        $sheet->getRowDimension(
                            $subTotalRow
                        )->setRowHeight(30);

                        $currentRow++;
                    }

                    if (count($lineGroups) === 0) {

                        $sheet->getStyle(
                            'B' . $currentRow . ':H' . $currentRow
                        )->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        $sheet->getRowDimension(
                            $currentRow
                        )->setRowHeight(38);

                        $currentRow++;

                    } else {

                        $dataEndRow = $currentRow - 1;

                        $sheet->getStyle(
                            'B' . $dataStartRow . ':H' . $dataEndRow
                        )->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);

                        $sheet->getStyle(
                            'B' . $dataStartRow . ':C' . $dataEndRow
                        )->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_LEFT
                            );

                        $sheet->getStyle(
                            'D' . $dataStartRow . ':H' . $dataEndRow
                        )->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_RIGHT
                            );

                        $sheet->getStyle(
                            'D' . $dataStartRow . ':H' . $dataEndRow
                        )->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }

                    // NOTE: the "GRAND TOTAL LINE ..." row + its styling
                    // block has been removed entirely — sections now end
                    // right after the data (or the "-" placeholder row).

                    $sheet->getRowDimension(
                        $currentRow
                    )->setRowHeight(15);

                    $currentRow++;
                }

                // style the overall grand total row(s) built in
                // collection(). $currentRow already points to the correct
                // sheet row here because the row counts produced above
                // mirror exactly what collection() emitted. Only D:F
                // (RM/FG/Total KG) get the number format — G/H (Man
                // Hours/Productivity) are intentionally left blank.
                if (count($this->overallTotals) > 0) {

                    foreach ($this->overallTotals as $total) {

                        $totalRow = $currentRow;

                        $sheet->setCellValue(
                            'B' . $totalRow,
                            $total['label']
                        );

                        $sheet->getStyle(
                            'B' . $totalRow . ':H' . $totalRow
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_MEDIUM,
                                ],
                            ],
                        ]);

                        $sheet->getStyle(
                            'D' . $totalRow . ':F' . $totalRow
                        )->getNumberFormat()
                            ->setFormatCode('#,##0.00');

                        $sheet->getRowDimension(
                            $totalRow
                        )->setRowHeight(30);

                        $currentRow++;
                    }
                }

                $highestRow =
                    $sheet->getHighestRow();

                $sheet->getColumnDimension('A')->setWidth(4);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(45);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(20);

                $sheet->getStyle(
                    'B1:H' . $highestRow
                )->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );
            },
        ];
    }
}