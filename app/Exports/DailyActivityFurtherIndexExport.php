<?php

namespace App\Exports;

use App\Models\DailyActivityFurther;
use App\Models\Line;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class DailyActivityFurtherIndexExport implements
    FromCollection,
    WithStyles,
    WithEvents
{
    protected $costCenterId;
    protected $psGroupId;
    protected $fromDate;
    protected $toDate;
    protected $departmentId;

    protected array $dates = [];
    protected $lines;
    protected array $manHours = [];

    public function __construct(
        $costCenterId,
        $psGroupId,
        $fromDate,
        $toDate,
        $departmentId = null
    ) {
        $this->costCenterId = $costCenterId;
        $this->psGroupId = $psGroupId;
        $this->departmentId = $departmentId;

        $start = Carbon::parse($fromDate)->startOfDay();
        $end = Carbon::parse($toDate)->startOfDay();

        if ($start->gt($end)) {
            $temp = $start;
            $start = $end;
            $end = $temp;
        }

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $this->dates[] = $date->copy();
        }

        $this->fromDate = $start->format('Y-m-d');
        $this->toDate = $end->format('Y-m-d');

        $this->lines = collect();
    }

    public function collection()
    {
        $this->lines = Line::query();

        if ($this->departmentId) {
            $this->lines->where(
                'department_id',
                $this->departmentId
            );
        } elseif (auth()->user()->department_id) {
            $this->lines->where(
                'department_id',
                auth()->user()->department_id
            );
        }


        $activities = DB::table('daily_activity_furthers as daf')
            ->leftJoin(
                'daily_activity_further_employees as dafe',
                'dafe.daily_activity_further_id',
                '=',
                'daf.id'
            )
            ->whereDate(
                'daf.tanggal',
                '>=',
                $this->fromDate
            )
            ->whereDate(
                'daf.tanggal',
                '<=',
                $this->toDate
            )
            ->whereNotNull('daf.line_id');

        if ($this->costCenterId) {
            $activities->where(
                'daf.cost_center_id',
                $this->costCenterId
            );
        }

        if ($this->psGroupId) {
            $activities->where(
                'daf.ps_group_id',
                $this->psGroupId
            );
        }

        if ($this->departmentId) {
            $activities->where(
                'daf.department_id',
                $this->departmentId
            );
        }

        $activities = $activities
            ->select(
                'daf.line_id',
                'daf.tanggal',
                DB::raw(
                    'COALESCE(SUM(dafe.jumlah_hk), 0) as total_man_hours'
                )
            )
            ->groupBy(
                'daf.line_id',
                'daf.tanggal'
            )
            ->orderBy('daf.line_id')
            ->orderBy('daf.tanggal')
            ->get();

        $this->manHours = [];

        foreach ($activities as $activity) {

            $lineId = $activity->line_id;

            $dateKey = Carbon::parse(
                $activity->tanggal
            )->format('Y-m-d');

            if (!isset($this->manHours[$lineId])) {
                $this->manHours[$lineId] = [];
            }

            if (!isset($this->manHours[$lineId][$dateKey])) {
                $this->manHours[$lineId][$dateKey] = 0;
            }

            $this->manHours[$lineId][$dateKey] +=
                (float) $activity->total_man_hours;
        }

        $rows = [];

        foreach ($this->lines as $line) {

            $row = [
                '',
                'LINE ' . $line->name,
            ];

            $lineTotal = 0;

            foreach ($this->dates as $date) {

                $dateKey = Carbon::parse($date)
                    ->format('Y-m-d');

                $value = 0;

                if (
                    isset($this->manHours[$line->id]) &&
                    isset($this->manHours[$line->id][$dateKey])
                ) {
                    $value =
                        (float) $this->manHours[$line->id][$dateKey];
                }

                $row[] = $value;

                $lineTotal += $value;
            }

            $row[] = $lineTotal;

            $rows[] = $row;
        }

        $grandTotalRow = [
            '',
            'GRAND TOTAL',
        ];

        $grandTotal = 0;

        foreach ($this->dates as $date) {

            $dateKey = Carbon::parse($date)
                ->format('Y-m-d');

            $dateTotal = 0;

            foreach ($this->lines as $line) {

                if (
                    isset($this->manHours[$line->id]) &&
                    isset($this->manHours[$line->id][$dateKey])
                ) {
                    $dateTotal +=
                        (float) $this->manHours[$line->id][$dateKey];
                }
            }

            $grandTotalRow[] = $dateTotal;

            $grandTotal += $dateTotal;
        }

        $grandTotalRow[] = $grandTotal;

        $rows[] = $grandTotalRow;

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

                if (count($this->dates) === 0) {
                    return;
                }

                $dateColumnStart = 3;

                $totalColumnIndex =
                    $dateColumnStart + count($this->dates);

                $lastColumnIndex = $totalColumnIndex;

                $lastColumn =
                    Coordinate::stringFromColumnIndex(
                        $lastColumnIndex
                    );

                $sheet->insertNewRowBefore(1, 4);

                $periodStart =
                    $this->dates[0]->copy();

                $periodEnd =
                    $this->dates[count($this->dates) - 1]->copy();

                $title =
                    'LAPORAN REKAPITULASI MAN HOURS FURTHER '
                    . strtoupper(
                        $periodStart->translatedFormat('F Y')
                    );

                $periodLabel =
                    $periodStart->translatedFormat('d F Y')
                    . ' S.D '
                    . $periodEnd->translatedFormat('d F Y');

                $sheet->mergeCells(
                    "B3:{$lastColumn}3"
                );

                $sheet->setCellValue(
                    'B3',
                    $title
                );

                $sheet->getStyle(
                    "B3:{$lastColumn}3"
                )->applyFromArray([
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

                $sheet->getRowDimension(3)
                    ->setRowHeight(30);

                $sheet->mergeCells(
                    "B4:{$lastColumn}4"
                );

                $sheet->setCellValue(
                    'B4',
                    'PERIODE : ' . strtoupper($periodLabel)
                );

                $sheet->getStyle(
                    "B4:{$lastColumn}4"
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' =>
                            Alignment::HORIZONTAL_LEFT,
                        'vertical' =>
                            Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension(4)
                    ->setRowHeight(24);

                $headerRow = 5;

                $sheet->setCellValue(
                    "B{$headerRow}",
                    'LINE'
                );

                $column = $dateColumnStart;

                foreach ($this->dates as $date) {

                    $letter =
                        Coordinate::stringFromColumnIndex(
                            $column
                        );

                    $sheet->setCellValue(
                        "{$letter}{$headerRow}",
                        $date->format('d/m')
                    );

                    $column++;
                }

                $totalColumn =
                    Coordinate::stringFromColumnIndex(
                        $totalColumnIndex
                    );

                $sheet->setCellValue(
                    "{$totalColumn}{$headerRow}",
                    'TOTAL'
                );

                $sheet->getStyle(
                    "B{$headerRow}:{$lastColumn}{$headerRow}"
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' =>
                            Alignment::HORIZONTAL_CENTER,
                        'vertical' =>
                            Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' =>
                            Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'D9D9D9',
                        ],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $sheet->getRowDimension($headerRow)
                    ->setRowHeight(28);

                $firstDataRow = 6;

                $lastDataRow =
                    $firstDataRow
                    + count($this->lines)
                    - 1;

                $grandTotalRow =
                    $lastDataRow + 1;

                if ($lastDataRow >= $firstDataRow) {

                    $sheet->getStyle(
                        "B{$firstDataRow}:{$lastColumn}{$lastDataRow}"
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
                        ],
                    ]);

                    $sheet->getStyle(
                        "B{$firstDataRow}:B{$lastDataRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        );

                    $sheet->getStyle(
                        "C{$firstDataRow}:{$lastColumn}{$lastDataRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );

                    $sheet->getStyle(
                        "C{$firstDataRow}:{$lastColumn}{$lastDataRow}"
                    )->getNumberFormat()
                        ->setFormatCode('#,##0.00');

                    for (
                        $row = $firstDataRow;
                        $row <= $lastDataRow;
                        $row++
                    ) {
                        $sheet->getRowDimension($row)
                            ->setRowHeight(26);
                    }
                }

                $sheet->getStyle(
                    "B{$grandTotalRow}:{$lastColumn}{$grandTotalRow}"
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
                    "C{$grandTotalRow}:{$lastColumn}{$grandTotalRow}"
                )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getRowDimension($grandTotalRow)
                    ->setRowHeight(28);

                $sheet->getColumnDimension('A')
                    ->setWidth(4);

                $sheet->getColumnDimension('B')
                    ->setWidth(18);

                for (
                    $i = 0;
                    $i < count($this->dates);
                    $i++
                ) {

                    $letter =
                        Coordinate::stringFromColumnIndex(
                            $dateColumnStart + $i
                        );

                    $sheet->getColumnDimension($letter)
                        ->setWidth(10);
                }

                $sheet->getColumnDimension($totalColumn)
                    ->setWidth(15);

                $sheet->freezePane('C6');

                $sheet->getPageSetup()->setOrientation(
                    PageSetup::ORIENTATION_LANDSCAPE
                );

                $sheet->getPageSetup()->setPaperSize(
                    PageSetup::PAPERSIZE_A4
                );

                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setRight(0.3);

                $sheet->getPageSetup()
                    ->setHorizontalCentered(true);
            },
        ];
    }
}