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
        $user = Auth::user();

        abort_unless($user, 403);

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if (
            !$isHrDepartment &&
            (int) $departmentId !== (int) $ownDepartment->id
        ) {
            abort(403);
        }

        $costCenters = CostCenter::where(
            'department_id',
            $departmentId
        )
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json($costCenters);
    }

    public function generalManagerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');
        $search = trim((string) $request->input('search'));

        $departments = Department::orderBy('name')->get();
        $outsourcings = Outsourcing::orderBy('name')->get();

        if (!$departmentId) {
            $costCenterId = null;
            $costCenters = collect();
        } else {
            $costCenters = CostCenter::where(
                'department_id',
                $departmentId
            )
                ->orderBy('name')
                ->get();
        }

        $allCostCenters = CostCenter::orderBy('code')->get();

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
                $costCenterId,
                $search
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

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
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
            $sausageUpah = DailyActivityDetail::query()
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
                    SUM(daily_activity_details.total_harga) AS total_upah
                ')
                ->groupBy(
                    'daily_activities.employee_id',
                    'daily_activities.cost_center_id'
                )
                ->get();

            $slaughterHouseUpah = DailyActivityDetailSlaughterHouse::query()
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
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.employee_id,
                    daily_activity_slaughter_houses.cost_center_id,
                    SUM(
                        daily_activity_detail_slaughter_houses.total_harga
                    ) AS total_upah
                ')
                ->groupBy(
                    'daily_activity_slaughter_houses.employee_id',
                    'daily_activity_slaughter_houses.cost_center_id'
                )
                ->get();

            foreach ($sausageUpah as $row) {
                if (!isset($costCenterUpah[$row->employee_id])) {
                    $costCenterUpah[$row->employee_id] = [];
                }

                if (!isset(
                    $costCenterUpah[$row->employee_id][$row->cost_center_id]
                )) {
                    $costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
                }

                $costCenterUpah[$row->employee_id][$row->cost_center_id] +=
                    (float) $row->total_upah;
            }

            foreach ($slaughterHouseUpah as $row) {
                if (!isset($costCenterUpah[$row->employee_id])) {
                    $costCenterUpah[$row->employee_id] = [];
                }

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

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F Y');

        return view(
            'pages.general_manager.penggajian-borongan.index',
            compact(
                'payrolls',
                'departments',
                'departmentId',
                'outsourcings',
                'outsourcingId',
                'costCenters',
                'allCostCenters',
                'costCenterId',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'search'
            )
        );
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $search = trim((string) $request->input('search'));

        $user = Auth::user();

        abort_unless(
            $user && $user->department_id,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if ($isHrDepartment) {
            $departments = Department::orderBy('name')->get();

            $departmentId = $request->input('department_id');

            $department = $departmentId
                ? Department::find($departmentId)
                : null;
        } else {
            $departmentId = $ownDepartment->id;

            $departments = collect();

            $department = $ownDepartment;
        }

        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        if ($departmentId) {
            $costCenters = CostCenter::where(
                'department_id',
                $departmentId
            )
                ->orderBy('name')
                ->get();
        } else {
            $costCenters = collect();
            $costCenterId = null;
        }

        if ($isHrDepartment) {
            $allCostCenters = CostCenter::orderBy('name')->get();
        } else {
            $allCostCenters = CostCenter::where(
                'department_id',
                $ownDepartment->id
            )
                ->orderBy('name')
                ->get();
        }

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
                $costCenterId,
                $search
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

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
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
            $sausageUpah = DailyActivityDetail::query()
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
                    SUM(daily_activity_details.total_harga) AS total_upah
                ')
                ->groupBy(
                    'daily_activities.employee_id',
                    'daily_activities.cost_center_id'
                )
                ->get();

            $slaughterHouseUpah = DailyActivityDetailSlaughterHouse::query()
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
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.employee_id,
                    daily_activity_slaughter_houses.cost_center_id,
                    SUM(
                        daily_activity_detail_slaughter_houses.total_harga
                    ) AS total_upah
                ')
                ->groupBy(
                    'daily_activity_slaughter_houses.employee_id',
                    'daily_activity_slaughter_houses.cost_center_id'
                )
                ->get();

            foreach ($sausageUpah as $row) {
                if (!isset($costCenterUpah[$row->employee_id])) {
                    $costCenterUpah[$row->employee_id] = [];
                }

                if (!isset(
                    $costCenterUpah[$row->employee_id][$row->cost_center_id]
                )) {
                    $costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
                }

                $costCenterUpah[$row->employee_id][$row->cost_center_id] +=
                    (float) $row->total_upah;
            }

            foreach ($slaughterHouseUpah as $row) {
                if (!isset($costCenterUpah[$row->employee_id])) {
                    $costCenterUpah[$row->employee_id] = [];
                }

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

        $outsourcings = Outsourcing::orderBy('name')->get();

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F Y');

        $departmentName = $department?->name;

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
                'allCostCenters',
                'costCenterId',
                'departmentName',
                'isHrDepartment',
                'departments',
                'departmentId',
                'search'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $search = trim((string) $request->input('search'));

        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $user = Auth::user();

        abort_unless(
            $user && $user->department_id,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if ($isHrDepartment) {
            $departmentId = $request->input('department_id');

            $departments = Department::orderBy('name')->get();

            $department = $departmentId
                ? Department::find($departmentId)
                : null;
        } else {
            $departmentId = $ownDepartment->id;

            $departments = collect();

            $department = $ownDepartment;
        }

        if ($departmentId) {
            $costCenters = CostCenter::where(
                'department_id',
                $departmentId
            )
                ->orderBy('name')
                ->get();
        } else {
            $costCenters = collect();
            $costCenterId = null;
        }

        if ($isHrDepartment) {
            $allCostCenters = CostCenter::orderBy('name')->get();
        } else {
            $allCostCenters = CostCenter::where(
                'department_id',
                $ownDepartment->id
            )
                ->orderBy('name')
                ->get();
        }

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
                $costCenterId,
                $search
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

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
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
            $sausageUpah = DailyActivityDetail::query()
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
                    SUM(daily_activity_details.total_harga) AS total_upah
                ')
                ->groupBy(
                    'daily_activities.employee_id',
                    'daily_activities.cost_center_id'
                )
                ->get();

            $slaughterHouseUpah = DailyActivityDetailSlaughterHouse::query()
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
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.employee_id,
                    daily_activity_slaughter_houses.cost_center_id,
                    SUM(
                        daily_activity_detail_slaughter_houses.total_harga
                    ) AS total_upah
                ')
                ->groupBy(
                    'daily_activity_slaughter_houses.employee_id',
                    'daily_activity_slaughter_houses.cost_center_id'
                )
                ->get();

            foreach ($sausageUpah as $row) {
                if (!isset($costCenterUpah[$row->employee_id])) {
                    $costCenterUpah[$row->employee_id] = [];
                }

                if (!isset(
                    $costCenterUpah[$row->employee_id][$row->cost_center_id]
                )) {
                    $costCenterUpah[$row->employee_id][$row->cost_center_id] = 0;
                }

                $costCenterUpah[$row->employee_id][$row->cost_center_id] +=
                    (float) $row->total_upah;
            }

            foreach ($slaughterHouseUpah as $row) {
                if (!isset($costCenterUpah[$row->employee_id])) {
                    $costCenterUpah[$row->employee_id] = [];
                }

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

        $outsourcings = Outsourcing::orderBy('name')->get();

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F Y');

        $departmentName = $department?->name;

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
                'allCostCenters',
                'costCenterId',
                'departmentName',
                'isHrDepartment',
                'departments',
                'departmentId',
                'search'
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
        $search = trim((string) $request->input('search'));

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
                $costCenterId,
                $search
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

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F Y');

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
        $search = trim((string) $request->input('search'));

        $user = Auth::user();

        abort_unless(
            $user && $user->department_id,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if ($isHrDepartment) {
            $departmentId = $request->input('department_id');
        } else {
            $departmentId = $ownDepartment->id;
        }

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
                $costCenterId,
                $search
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

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F Y');

        $department = $departmentId
            ? Department::find($departmentId)
            : null;

        $departmentName = $department->name ?? 'Semua Department';

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
        $search = trim((string) $request->input('search'));

        $user = Auth::user();

        abort_unless(
            $user && $user->department_id,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if ($isHrDepartment) {
            $departmentId = $request->input('department_id');
        } else {
            $departmentId = $ownDepartment->id;
        }

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
                $costCenterId,
                $search
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

                if ($search !== '') {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F Y');

        $department = $departmentId
            ? Department::find($departmentId)
            : null;

        $departmentName = $department->name ?? 'Semua Department';

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
        $search = trim((string) $request->input('search'));

        $user = Auth::user();

        abort_unless(
            $user && $user->department_id,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if ($isHrDepartment) {
            $departmentId = $request->input('department_id');
        } else {
            $departmentId = $ownDepartment->id;
        }

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId
                    ? (int) $departmentId
                    : null,
                $outsourcingId
                    ? (int) $outsourcingId
                    : null,
                $costCenterId
                    ? (int) $costCenterId
                    : null,
                $search !== ''
                    ? $search
                    : null
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
        $search = trim((string) $request->input('search'));

        $user = Auth::user();

        abort_unless(
            $user && $user->department_id,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $ownDepartment = Department::findOrFail($user->department_id);

        $isHrDepartment =
            strtolower(trim($ownDepartment->name)) ===
            'personalia dan general affair';

        if ($isHrDepartment) {
            $departmentId = $request->input('department_id');
        } else {
            $departmentId = $ownDepartment->id;
        }

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId
                    ? (int) $departmentId
                    : null,
                $outsourcingId
                    ? (int) $outsourcingId
                    : null,
                $costCenterId
                    ? (int) $costCenterId
                    : null,
                $search !== ''
                    ? $search
                    : null
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
        $search = trim((string) $request->input('search'));

        $periodLabel = Carbon::create(
            $year,
            $month,
            1
        )->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId
                    ? (int) $departmentId
                    : null,
                $outsourcingId
                    ? (int) $outsourcingId
                    : null,
                $costCenterId
                    ? (int) $costCenterId
                    : null,
                $search !== ''
                    ? $search
                    : null
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
        );
    }
}