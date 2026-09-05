<?php

namespace App\Http\Controllers;

use App\Exports\PenggajianBoronganExport;
use App\Models\CostCenter;
use App\Models\DailyActivityDetail;
use App\Models\DailyActivityDetailSlaughterHouse;
use App\Models\Department;
use App\Models\Outsourcing;
use App\Models\PenggajianBorongan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PenggajianBoronganController extends Controller
{

    public function getCostCenters($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($costCenters);
    }
    
    public function generalManagerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departments = Department::orderBy('name')->get();
        $outsourcings = Outsourcing::orderBy('name')->get();

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use (
                $departmentId,
                $outsourcingId,
                $costCenterId
            ) {
                $q->where('employee_status', 'borongan');

                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            });

        $grandTotalKg = (clone $query)->sum('total_kg');
        $grandTotalUpah = (clone $query)->sum('total_upah');

        $payrolls = $query
            ->orderBy('employee_id')
            ->paginate(10)
            ->withQueryString();

        $employeeIds = $payrolls->getCollection()
            ->pluck('employee_id')
            ->unique()
            ->values();

        $costCenterUpah = [];

        if ($employeeIds->isNotEmpty()) {

            $sausageQuery = DailyActivityDetail::query()
                ->join(
                    'daily_activities',
                    'daily_activities.id',
                    '=',
                    'daily_activity_details.daily_activity_id'
                )
                ->whereIn('daily_activities.employee_id', $employeeIds)
                ->whereMonth('daily_activities.tanggal', $month)
                ->whereYear('daily_activities.tanggal', $year);

            $slaughterHouseQuery = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->whereIn(
                    'daily_activity_slaughter_houses.employee_id',
                    $employeeIds
                )
                ->whereMonth(
                    'daily_activity_slaughter_houses.tanggal',
                    $month
                )
                ->whereYear(
                    'daily_activity_slaughter_houses.tanggal',
                    $year
                );

            if ($departmentId) {

                $sausageQuery->where(
                    'daily_activities.department_id',
                    $departmentId
                );

                $slaughterHouseQuery->where(
                    'daily_activity_slaughter_houses.department_id',
                    $departmentId
                );
            }

            $sausageUpah = $sausageQuery
                ->selectRaw('
                    daily_activities.employee_id,
                    daily_activities.cost_center_id,
                    SUM(daily_activity_details.total_harga) as total_upah
                ')
                ->groupBy(
                    'daily_activities.employee_id',
                    'daily_activities.cost_center_id'
                )
                ->get();

            $slaughterHouseUpah = $slaughterHouseQuery
                ->selectRaw('
                    daily_activity_slaughter_houses.employee_id,
                    daily_activity_slaughter_houses.cost_center_id,
                    SUM(daily_activity_detail_slaughter_houses.total_harga) as total_upah
                ')
                ->groupBy(
                    'daily_activity_slaughter_houses.employee_id',
                    'daily_activity_slaughter_houses.cost_center_id'
                )
                ->get();

            foreach ($sausageUpah as $row) {

                if (!isset(
                    $costCenterUpah[$row->employee_id][$row->cost_center_id]
                )) {
                    $costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
                }

                $costCenterUpah[$row->employee_id][$row->cost_center_id] +=
                    (float) $row->total_upah;
            }

            foreach ($slaughterHouseUpah as $row) {

                if (!isset(
                    $costCenterUpah[$row->employee_id][$row->cost_center_id]
                )) {
                    $costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
                }

                $costCenterUpah[$row->employee_id][$row->cost_center_id] +=
                    (float) $row->total_upah;
            }
        }

        foreach ($payrolls as $payroll) {
            $payroll->cost_center_upah =
                $costCenterUpah[$payroll->employee_id] ?? [];
        }

        if ($departmentId) {
            $costCenters = CostCenter::where(
                'department_id',
                $departmentId
            )
                ->orderBy('name')
                ->get();
        } else {
            $costCenters = CostCenter::orderBy('name')
                ->get();
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.general_manager.penggajian-borongan.index',
            compact(
                'payrolls',
                'departments',
                'departmentId',
                'outsourcings',
                'outsourcingId',
                'costCenterId',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'costCenters'
            )
        );
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use (
                $departmentId,
                $outsourcingId,
                $costCenterId
            ) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            })
            ->orderBy('employee_id');

        $grandTotalKg = (clone $query)->sum('total_kg');
        $grandTotalUpah = (clone $query)->sum('total_upah');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $employeeIds = $payrolls->getCollection()
            ->pluck('employee_id')
            ->unique()
            ->values();

        $costCenterUpah = [];

        if ($employeeIds->isNotEmpty()) {

            if (strtolower(trim($department->name)) === 'sausage') {

                $activityUpah = DailyActivityDetail::query()
                    ->join(
                        'daily_activities',
                        'daily_activities.id',
                        '=',
                        'daily_activity_details.daily_activity_id'
                    )
                    ->whereIn(
                        'daily_activities.employee_id',
                        $employeeIds
                    )
                    ->where(
                        'daily_activities.department_id',
                        $departmentId
                    )
                    ->whereMonth(
                        'daily_activities.tanggal',
                        $month
                    )
                    ->whereYear(
                        'daily_activities.tanggal',
                        $year
                    )
                    ->selectRaw('
                        daily_activities.employee_id,
                        daily_activities.cost_center_id,
                        SUM(daily_activity_details.total_harga) as total_upah
                    ')
                    ->groupBy(
                        'daily_activities.employee_id',
                        'daily_activities.cost_center_id'
                    )
                    ->get();

            } elseif (
                strtolower(trim($department->name)) === 'slaughter house'
            ) {

                $activityUpah = DailyActivityDetailSlaughterHouse::query()
                    ->join(
                        'daily_activity_slaughter_houses',
                        'daily_activity_slaughter_houses.id',
                        '=',
                        'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                    )
                    ->whereIn(
                        'daily_activity_slaughter_houses.employee_id',
                        $employeeIds
                    )
                    ->where(
                        'daily_activity_slaughter_houses.department_id',
                        $departmentId
                    )
                    ->whereMonth(
                        'daily_activity_slaughter_houses.tanggal',
                        $month
                    )
                    ->whereYear(
                        'daily_activity_slaughter_houses.tanggal',
                        $year
                    )
                    ->selectRaw('
                        daily_activity_slaughter_houses.employee_id,
                        daily_activity_slaughter_houses.cost_center_id,
                        SUM(daily_activity_detail_slaughter_houses.total_harga) as total_upah
                    ')
                    ->groupBy(
                        'daily_activity_slaughter_houses.employee_id',
                        'daily_activity_slaughter_houses.cost_center_id'
                    )
                    ->get();

            } else {
                $activityUpah = collect();
            }

            foreach ($activityUpah as $row) {
                $costCenterUpah[$row->employee_id][$row->cost_center_id] =
                    (float) $row->total_upah;
            }
        }

        foreach ($payrolls as $payroll) {
            $payroll->cost_center_upah =
                $costCenterUpah[$payroll->employee_id] ?? [];
        }

        $outsourcings = Outsourcing::orderBy('name')->get();

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = $department->name;

        return view(
            'pages.admin_production.penggajian-borongan.index',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'outsourcings',
                'outsourcingId',
                'costCenters',
                'costCenterId',
                'departmentName'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use (
                $departmentId,
                $outsourcingId,
                $costCenterId
            ) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            })
            ->orderBy('employee_id');

        $grandTotalKg = (clone $query)->sum('total_kg');
        $grandTotalUpah = (clone $query)->sum('total_upah');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $employeeIds = $payrolls->getCollection()
            ->pluck('employee_id')
            ->unique()
            ->values();

        $costCenterUpah = [];

        if ($employeeIds->isNotEmpty()) {

            if (strtolower(trim($department->name)) === 'sausage') {

                $activityUpah = DailyActivityDetail::query()
                    ->join(
                        'daily_activities',
                        'daily_activities.id',
                        '=',
                        'daily_activity_details.daily_activity_id'
                    )
                    ->whereIn(
                        'daily_activities.employee_id',
                        $employeeIds
                    )
                    ->where(
                        'daily_activities.department_id',
                        $departmentId
                    )
                    ->whereMonth(
                        'daily_activities.tanggal',
                        $month
                    )
                    ->whereYear(
                        'daily_activities.tanggal',
                        $year
                    )
                    ->selectRaw('
                        daily_activities.employee_id,
                        daily_activities.cost_center_id,
                        SUM(daily_activity_details.total_harga) as total_upah
                    ')
                    ->groupBy(
                        'daily_activities.employee_id',
                        'daily_activities.cost_center_id'
                    )
                    ->get();

            } elseif (
                strtolower(trim($department->name)) === 'slaughter house'
            ) {

                $activityUpah = DailyActivityDetailSlaughterHouse::query()
                    ->join(
                        'daily_activity_slaughter_houses',
                        'daily_activity_slaughter_houses.id',
                        '=',
                        'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                    )
                    ->whereIn(
                        'daily_activity_slaughter_houses.employee_id',
                        $employeeIds
                    )
                    ->where(
                        'daily_activity_slaughter_houses.department_id',
                        $departmentId
                    )
                    ->whereMonth(
                        'daily_activity_slaughter_houses.tanggal',
                        $month
                    )
                    ->whereYear(
                        'daily_activity_slaughter_houses.tanggal',
                        $year
                    )
                    ->selectRaw('
                        daily_activity_slaughter_houses.employee_id,
                        daily_activity_slaughter_houses.cost_center_id,
                        SUM(daily_activity_detail_slaughter_houses.total_harga) as total_upah
                    ')
                    ->groupBy(
                        'daily_activity_slaughter_houses.employee_id',
                        'daily_activity_slaughter_houses.cost_center_id'
                    )
                    ->get();

            } else {

                $activityUpah = collect();

            }

            foreach ($activityUpah as $row) {

                $costCenterUpah[$row->employee_id][$row->cost_center_id] =
                    (float) $row->total_upah;
            }
        }

        foreach ($payrolls as $payroll) {

            $payroll->cost_center_upah =
                $costCenterUpah[$payroll->employee_id] ?? [];
        }

        $outsourcings = Outsourcing::orderBy('name')->get();

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = $department->name;

        return view(
            'pages.manager.penggajian-borongan.index',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'outsourcings',
                'outsourcingId',
                'costCenters',
                'costCenterId',
                'departmentName'
            )
        );
    }

    public function exportPdfGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId) {
                $q->where('employee_status', 'borongan');

                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            });

        $payrolls = $query
            ->orderBy('employee_id')
            ->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = 'Semua Department';

        if ($departmentId) {
            $department = Department::find($departmentId);
            $departmentName = $department->name ?? '-';
        }

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $costCenterName = 'Semua Cost Center';

        if ($costCenterId) {
            $costCenter = CostCenter::find($costCenterId);
            $costCenterName = $costCenter->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.general_manager.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName',
                'outsourcingName',
                'costCenterName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdfManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $costCenterName = 'Semua Cost Center';

        if ($costCenterId) {
            $costCenter = CostCenter::find($costCenterId);
            $costCenterName = $costCenter->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.manager.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName',
                'outsourcingName',
                'costCenterName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdf(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $costCenterName = 'Semua Cost Center';

        if ($costCenterId) {
            $costCenter = CostCenter::find($costCenterId);
            $costCenterName = $costCenter->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.admin_production.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName',
                'outsourcingName',
                'costCenterName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }

    public function exportExcel(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId,
                $outsourcingId,
                $costCenterId ? (int) $costCenterId : null
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
        );
    }

    public function exportExcelManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId,
                $outsourcingId,
                $costCenterId ? (int) $costCenterId : null
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
        );
    }

    public function exportExcelGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId ? (int) $departmentId : null,
                $outsourcingId ? (int) $outsourcingId : null,
                $costCenterId ? (int) $costCenterId : null
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
        );
    }
}