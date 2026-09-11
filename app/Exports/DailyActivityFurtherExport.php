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

    public function collection()
    {
        $query = DailyActivityDetailFurther::query()
            ->with([
                'product',
                'dailyActivityFurther.line',
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
                'daily_activity_furthers.tanggal'
            )
            ->orderBy(
                'daily_activity_detail_furthers.id'
            )
            ->select(
                'daily_activity_detail_furthers.*'
            );

        $details = $query->get();

        $grouped = [];

        foreach ($details as $detail) {

            $daf = $detail->dailyActivityFurther;

            if (!$daf) {
                continue;
            }

            $lineId = $daf->line_id;

            $tanggal = Carbon::parse(
                $daf->tanggal
            )->format('Y-m-d');

            if (!isset($grouped[$lineId])) {
                $grouped[$lineId] = [];
            }

            if (!isset($grouped[$lineId][$tanggal])) {

                $grouped[$lineId][$tanggal] = [
                    'tanggal' => $daf->tanggal,
                    'line_id' => $lineId,
                    'line_name' => $daf->line->name ?? '-',
                    'employees' => [],
                    'products' => [],
                ];
            }

            if (!isset(
                $grouped[$lineId][$tanggal]['employees'][$detail->employee_id]
            )) {

                $grouped[$lineId][$tanggal]['employees'][$detail->employee_id] = [
                    'man_power' => (float) $detail->man_power,
                ];
            }

            $productKey = $detail->product_id;

            if (!isset(
                $grouped[$lineId][$tanggal]['products'][$productKey]
            )) {

                $grouped[$lineId][$tanggal]['products'][$productKey] = [
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

        $rows = [];

        foreach ($this->lines as $line) {

            $rows[] = [
                '',
                'LINE ' . $line->name,
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

            $lineTotalRm = 0;
            $lineTotalFg = 0;
            $lineTotalKg = 0;
            $lineTotalManHours = 0;

            $lineGroups = [];

            if (isset($grouped[$line->id])) {
                $lineGroups = $grouped[$line->id];
            }

            if (count($lineGroups) === 0) {

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
                    'GRAND TOTAL',
                    '',
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

                    $rows[] = [
                        '',
                        'GRAND TOTAL ' .
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

                    $lineTotalRm += $dateTotalRm;
                    $lineTotalFg += $dateTotalFg;
                    $lineTotalKg += $dateTotalKg;
                    $lineTotalManHours += $manHours;
                }

                $lineProductivity = $lineTotalManHours > 0
                    ? $lineTotalFg / $lineTotalManHours
                    : 0;

                $rows[] = [
                    '',
                    'GRAND TOTAL LINE ' . $line->name,
                    '',
                    $lineTotalRm,
                    $lineTotalFg,
                    $lineTotalKg,
                    $lineTotalManHours,
                    $lineProductivity,
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
            }
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

                foreach ($this->lines as $line) {

                    $lineRow = $currentRow;

                    $sheet->mergeCells(
                        'B' . $lineRow . ':H' . $lineRow
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

                    $lineGroups = [];

                    if (isset(
                        $this->groupedDetails[$line->id]
                    )) {
                        $lineGroups =
                            $this->groupedDetails[$line->id];
                    }

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

                        $dateTotalRow = $currentRow;

                        $sheet->getStyle(
                            'B' . $dateTotalRow . ':H' . $dateTotalRow
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
                            'D' . $dateTotalRow . ':H' . $dateTotalRow
                        )->getNumberFormat()
                            ->setFormatCode('#,##0.00');

                        $sheet->getRowDimension(
                            $dateTotalRow
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

                    $grandTotalLineRow = $currentRow;

                    $sheet->getStyle(
                        'B' . $grandTotalLineRow . ':H' . $grandTotalLineRow
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
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
                        'D' . $grandTotalLineRow . ':H' . $grandTotalLineRow
                    )->getNumberFormat()
                        ->setFormatCode('#,##0.00');

                    $sheet->getRowDimension(
                        $grandTotalLineRow
                    )->setRowHeight(30);

                    $currentRow++;

                    $sheet->getRowDimension(
                        $currentRow
                    )->setRowHeight(15);

                    $currentRow++;
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