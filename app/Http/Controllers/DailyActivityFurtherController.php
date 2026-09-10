<?php

namespace App\Http\Controllers;

use App\Exports\DailyActivityFurtherExport;
use App\Exports\DailyActivityFurtherIndexExport;
use App\Models\Attendance;
use App\Models\CostCenter;
use App\Models\DailyActivityFurther;
use App\Models\DailyActivityDetailFurther;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Line;
use App\Models\ProcessType;
use App\Models\Product;
use App\Models\PsGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DailyActivityFurtherController extends Controller
{
    public function create()
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $costCenterList = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $processTypes = ProcessType::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $lines = Line::orderBy('name')
            ->where('department_id', $departmentId)
            ->get();

        return view(
            'pages.admin_production.daily_activity_further.create',
            compact(
                'department',
                'costCenterList',
                'lines',
                'processTypes'
            )
        );
    }

    public function getManHours(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'exists:employees,id'],
        ]);

        $manHours = 0;

        foreach ($request->employee_ids as $employeeId) {

            $attendance = Attendance::where('employee_id', $employeeId)
                ->whereDate('date', $request->tanggal)
                ->where('status', 'hadir')
                ->first();

            if ($attendance) {
                $manHours += (float) $attendance->jumlah_hk;
            }
        }

        return response()->json([
            'man_hours' => $manHours,
        ]);
    }

    public function getCostCenters($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);

        return response()->json($costCenters);
    }

    public function getProducts(Request $request, $costCenterId)
    {
        $request->validate([
            'process_type_id' => 'required|exists:process_types,id',
        ]);

        $departmentId = auth()->user()->department_id;

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($costCenterId);

        $products = Product::where('cost_center_id', $costCenter->id)
            ->where('process_type_id', $request->process_type_id)
            ->orderBy('material_name')
            ->get([
                'id',
                'material_name',
                'material_code',
            ]);

        return response()->json($products);
    }

    public function getPsGroups($costCenterId)
    {
        $departmentId = auth()->user()->department_id;

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($costCenterId);

        $groups = PsGroup::where('cost_center_id', $costCenter->id)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return response()->json($groups);
    }

    public function getEmployees($costCenterId, $psGroupId)
    {
        $departmentId = auth()->user()->department_id;

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $employees = Employee::where('cost_center_id', $costCenter->id)
            ->where('ps_group_id', $psGroup->id)
            ->orderBy('name')
            ->get([
                'id',
                'nik',
                'name',
                'employee_status',
            ]);

        return response()->json($employees);
    }

    public function getLines($departmentId)
    {
        $lines = Line::where('department_id', $departmentId)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        return response()->json($lines);
    }

    public function costCentersByDepartment($departmentId)
    {
        return CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();
    }

    public function psGroupsByCostCenter($costCenterId)
    {
        return PsGroup::where('cost_center_id', $costCenterId)
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'ps_group_id' => ['required', 'exists:ps_groups,id'],
            'line_id' => ['required', 'exists:lines,id'],
            'employees' => ['required', 'array', 'min:1'],
            'employees.*.employee_id' => ['required', 'exists:employees,id'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.total_kg_rm' => ['required', 'numeric', 'min:0'],
            'details.*.total_kg_fg' => ['required', 'numeric', 'min:0'],
        ]);

        $departmentId = auth()->user()->department_id;

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($request->cost_center_id);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($request->ps_group_id);

        Line::where('department_id', $departmentId)
            ->findOrFail($request->line_id);

        ProcessType::where('department_id', $departmentId)
            ->findOrFail($request->process_type_id);

        $employeeHk = [];

        foreach ($request->employees as $employeeData) {
            $employee = Employee::where('id', $employeeData['employee_id'])
                ->where('cost_center_id', $costCenter->id)
                ->where('ps_group_id', $psGroup->id)
                ->first();

            if (!$employee) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Employee tidak sesuai dengan Cost Center atau PS Group.'
                    );
            }

            if (!isset($employeeHk[$employee->id])) {
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $request->tanggal)
                    ->where('status', 'hadir')
                    ->first();

                if (!$attendance) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Employee ' . $employee->name . ' belum memiliki absensi hadir pada tanggal tersebut.'
                        );
                }

                $employeeHk[$employee->id] = (float) $attendance->jumlah_hk;
            }
        }

        $manHours = array_sum($employeeHk);

        if ($manHours <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Total Man Hours harus lebih dari 0.');
        }

        $totalKgFg = 0;

        foreach ($request->details as $detail) {
            $totalKgFg += (float) $detail['total_kg_fg'];
        }

        $productivity = $totalKgFg / $manHours;

        DB::transaction(function () use (
            $request,
            $departmentId,
            $costCenter,
            $psGroup,
            $employeeHk,
            $manHours,
            $productivity
        ) {
            $dailyActivityFurther = DailyActivityFurther::create([
                'tanggal' => $request->tanggal,
                'department_id' => $departmentId,
                'cost_center_id' => $costCenter->id,
                'ps_group_id' => $psGroup->id,
                'line_id' => $request->line_id,
                'input_by' => auth()->user()->id,
            ]);

            foreach ($employeeHk as $employeeId => $hk) {
                $dailyActivityFurther->employees()->attach($employeeId, [
                    'jumlah_hk' => $hk,
                ]);
            }

            foreach ($request->details as $detail) {
                $dailyActivityFurther->details()->create([
                    'product_id' => $detail['product_id'],
                    'total_kg_rm' => (float) $detail['total_kg_rm'],
                    'total_kg_fg' => (float) $detail['total_kg_fg'],
                    'man_power' => $manHours,
                    'productivity' => $productivity,
                ]);
            }
        });

        return redirect()
            ->route('admin-production.daily-activity-further.index')
            ->with('success', 'Daily Activity berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $department = auth()->user()->department;

        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');

        $query = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_furthers.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_furthers.cost_center_id')
            ->leftJoin('ps_groups', 'ps_groups.id', '=', 'daily_activity_furthers.ps_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_furthers.line_id')
            ->where('cost_centers.department_id', $department->id);

        if ($request->filled('cost_center_id')) {
            $query->where('daily_activity_furthers.cost_center_id', $request->cost_center_id);
        }

        if ($request->filled('ps_group_id')) {
            $query->where('daily_activity_furthers.ps_group_id', $request->ps_group_id);
        }

        if ($request->filled('line_id')) {
            $query->where('daily_activity_furthers.line_id', $request->line_id);
        }

        if ($dateFrom) {
            $query->whereDate('daily_activity_furthers.tanggal', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('daily_activity_furthers.tanggal', '<=', $dateTo);
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activity_furthers.tanggal', today());
                break;

            case 'week':
                $query->whereBetween('daily_activity_furthers.tanggal', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereMonth('daily_activity_furthers.tanggal', now()->month)
                    ->whereYear('daily_activity_furthers.tanggal', now()->year);
                break;
        }

        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                lines.id as line_id,
                lines.name as line_name,
                SUM(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm,
                SUM(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name',
                'lines.id',
                'lines.name'
            )
            ->orderBy('cost_centers.name')
            ->orderBy('ps_groups.id', 'ASC')
            ->paginate(10);

        $grandTotalKg = 0;
        $grandTotalRupiah = 0;

        foreach ($summaries as $summary) {
            $grandTotalKg += $summary->total_kg;
        }

        $costCenters = CostCenter::where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        $lines = Line::where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        return view('pages.admin_production.daily_activity_further.index', compact(
            'department',
            'costCenters',
            'lines',
            'summaries',
            'grandTotalKg',
            'grandTotalRupiah',
            'dateFrom',
            'dateTo'
        ));
    }

    public function generalManagerIndex(Request $request)
    {
        $dateFrom = $request->input('start_date');
        $dateTo = $request->input('end_date');

        $query = DailyActivityDetailFurther::query()
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->join(
                'departments',
                'departments.id',
                '=',
                'daily_activity_furthers.department_id'
            )
            ->join(
                'cost_centers',
                'cost_centers.id',
                '=',
                'daily_activity_furthers.cost_center_id'
            )
            ->leftJoin(
                'ps_groups',
                'ps_groups.id',
                '=',
                'daily_activity_furthers.ps_group_id'
            )
            ->leftJoin(
                'lines',
                'lines.id',
                '=',
                'daily_activity_furthers.line_id'
            );

        if ($request->filled('department_id')) {
            $query->where(
                'daily_activity_furthers.department_id',
                $request->department_id
            );
        }

        if ($request->filled('cost_center_id')) {
            $query->where(
                'daily_activity_furthers.cost_center_id',
                $request->cost_center_id
            );
        }

        if ($request->filled('ps_group_id')) {
            $query->where(
                'daily_activity_furthers.ps_group_id',
                $request->ps_group_id
            );
        }

        if ($request->filled('line_id')) {
            $query->where(
                'daily_activity_furthers.line_id',
                $request->line_id
            );
        }

        if ($dateFrom) {
            $query->whereDate(
                'daily_activity_furthers.tanggal',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'daily_activity_furthers.tanggal',
                '<=',
                $dateTo
            );
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate(
                    'daily_activity_furthers.tanggal',
                    today()
                );
                break;

            case 'week':
                $query->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]
                );
                break;

            case 'month':
                $query->whereMonth(
                    'daily_activity_furthers.tanggal',
                    now()->month
                )->whereYear(
                    'daily_activity_furthers.tanggal',
                    now()->year
                );
                break;
        }

        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                lines.id as line_id,
                lines.name as line_name,
                SUM(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm,
                SUM(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name',
                'lines.id',
                'lines.name'
            )
            ->orderBy('departments.name')
            ->orderBy('cost_centers.name')
            ->orderBy('ps_groups.name')
            ->orderBy('lines.name')
            ->paginate(10)
            ->withQueryString();

        $grandTotalKgRm = 0;
        $grandTotalKgFg = 0;

        foreach ($summaries as $summary) {
            $grandTotalKgRm += (float) $summary->total_kg_rm;
            $grandTotalKgFg += (float) $summary->total_kg_fg;
        }

        $grandTotalKg = $grandTotalKgRm + $grandTotalKgFg;

        $departments = Department::orderBy('name')->get();

        $costCenters = CostCenter::orderBy('name')->get();

        $lines = Line::orderBy('name')->get();

        return view(
            'pages.general_manager.daily_activity_further.index',
            compact(
                'departments',
                'costCenters',
                'lines',
                'summaries',
                'grandTotalKgRm',
                'grandTotalKgFg',
                'grandTotalKg',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $department = auth()->user()->department;

        $dateFrom = $request->input('start_date');
        $dateTo = $request->input('end_date');

        $query = DailyActivityDetailFurther::query()
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->join(
                'departments',
                'departments.id',
                '=',
                'daily_activity_furthers.department_id'
            )
            ->join(
                'cost_centers',
                'cost_centers.id',
                '=',
                'daily_activity_furthers.cost_center_id'
            )
            ->leftJoin(
                'ps_groups',
                'ps_groups.id',
                '=',
                'daily_activity_furthers.ps_group_id'
            )
            ->leftJoin(
                'lines',
                'lines.id',
                '=',
                'daily_activity_furthers.line_id'
            )
            ->where('cost_centers.department_id', $department->id);

        if ($request->filled('cost_center_id')) {
            $query->where(
                'daily_activity_furthers.cost_center_id',
                $request->cost_center_id
            );
        }

        if ($request->filled('ps_group_id')) {
            $query->where(
                'daily_activity_furthers.ps_group_id',
                $request->ps_group_id
            );
        }

        if ($request->filled('line_id')) {
            $query->where(
                'daily_activity_furthers.line_id',
                $request->line_id
            );
        }

        if ($dateFrom) {
            $query->whereDate(
                'daily_activity_furthers.tanggal',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'daily_activity_furthers.tanggal',
                '<=',
                $dateTo
            );
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate(
                    'daily_activity_furthers.tanggal',
                    today()
                );
                break;

            case 'week':
                $query->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]
                );
                break;

            case 'month':
                $query->whereMonth(
                    'daily_activity_furthers.tanggal',
                    now()->month
                )->whereYear(
                    'daily_activity_furthers.tanggal',
                    now()->year
                );
                break;
        }

        $totalQuery = clone $query;

        $grandTotalKgRm = (float) $totalQuery
            ->selectRaw(
                'COALESCE(SUM(daily_activity_detail_furthers.total_kg_rm), 0) as total'
            )
            ->value('total');

        $grandTotalKgFg = (float) $totalQuery
            ->selectRaw(
                'COALESCE(SUM(daily_activity_detail_furthers.total_kg_fg), 0) as total'
            )
            ->value('total');

        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                lines.id as line_id,
                lines.name as line_name,
                SUM(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm,
                SUM(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name',
                'lines.id',
                'lines.name'
            )
            ->orderBy('cost_centers.name')
            ->orderBy('ps_groups.id', 'ASC')
            ->paginate(10)
            ->withQueryString();

        $costCenters = CostCenter::where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        $lines = Line::where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        return view('pages.manager.daily_activity_further.index', compact(
            'department',
            'costCenters',
            'lines',
            'summaries',
            'grandTotalKgRm',
            'grandTotalKgFg',
            'dateFrom',
            'dateTo'
        ));
    }

    public function detail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $departmentId = auth()->user()->department_id;

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $line = null;

        if ($lineId) {
            $line = Line::where('department_id', $departmentId)
                ->findOrFail($lineId);
        }

        $dateFrom = $request->input(
            'date_from',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dateTo = $request->input(
            'date_to',
            now()->format('Y-m-d')
        );

        $query = DailyActivityDetailFurther::query()
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'daily_activity_detail_furthers.product_id'
            )
            ->join(
                'users',
                'users.id',
                '=',
                'daily_activity_furthers.input_by'
            )
            ->leftJoin(
                'lines',
                'lines.id',
                '=',
                'daily_activity_furthers.line_id'
            )
            ->where(
                'daily_activity_furthers.department_id',
                $departmentId
            )
            ->where(
                'daily_activity_furthers.cost_center_id',
                $costCenter->id
            )
            ->where(
                'daily_activity_furthers.ps_group_id',
                $psGroup->id
            )
            ->whereBetween(
                'daily_activity_furthers.tanggal',
                [$dateFrom, $dateTo]
            );

        if ($lineId) {
            $query->where(
                'daily_activity_furthers.line_id',
                $lineId
            );
        }

        $details = $query
            ->select(
                'daily_activity_detail_furthers.id',
                'daily_activity_furthers.id as daily_activity_further_id',
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.line_id',
                'users.name as user_name',
                'lines.name as line_name',
                'products.id as product_id',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_furthers.total_kg_rm',
                'daily_activity_detail_furthers.total_kg_fg',
                'daily_activity_detail_furthers.man_power',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.line_id')
            ->orderBy('daily_activity_detail_furthers.created_at')
            ->paginate(100)
            ->withQueryString();

        // Ambil nama-nama employee per header dalam 1 query (hindari N+1)
        $furtherIds = $details->pluck('daily_activity_further_id')->unique();

        $employeeNamesByFurtherId = DailyActivityFurther::whereIn('id', $furtherIds)
            ->with('employees:id,name')
            ->get()
            ->mapWithKeys(function ($further) {
                return [
                    $further->id => $further->employees->pluck('name')->join(', '),
                ];
            });

        return view(
            'pages.admin_production.daily_activity_further.detail',
            compact(
                'costCenter',
                'psGroup',
                'line',
                'details',
                'employeeNamesByFurtherId',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function generalManagerDetail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $line = null;

        if ($lineId) {
            $line = Line::where('department_id', $costCenter->department_id)
                ->findOrFail($lineId);
        }

        $dateFrom = $request->input(
            'date_from',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dateTo = $request->input(
            'date_to',
            now()->format('Y-m-d')
        );

        $query = DailyActivityDetailFurther::query()
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'daily_activity_detail_furthers.product_id'
            )
            ->join(
                'users',
                'users.id',
                '=',
                'daily_activity_furthers.input_by'
            )
            ->leftJoin(
                'lines',
                'lines.id',
                '=',
                'daily_activity_furthers.line_id'
            )
            ->where(
                'daily_activity_furthers.department_id',
                $costCenter->department_id
            )
            ->where(
                'daily_activity_furthers.cost_center_id',
                $costCenter->id
            )
            ->where(
                'daily_activity_furthers.ps_group_id',
                $psGroup->id
            )
            ->whereBetween(
                'daily_activity_furthers.tanggal',
                [$dateFrom, $dateTo]
            );

        if ($lineId) {
            $query->where(
                'daily_activity_furthers.line_id',
                $lineId
            );
        }

        $details = $query
            ->select(
                'daily_activity_detail_furthers.id',
                'daily_activity_furthers.id as daily_activity_further_id',
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.line_id',
                'users.name as user_name',
                'lines.name as line_name',
                'products.id as product_id',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_furthers.total_kg_rm',
                'daily_activity_detail_furthers.total_kg_fg',
                'daily_activity_detail_furthers.man_power',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.line_id')
            ->orderBy('daily_activity_detail_furthers.created_at')
            ->paginate(100)
            ->withQueryString();

        $furtherIds = $details->pluck('daily_activity_further_id')->unique();

        $employeeNamesByFurtherId = DailyActivityFurther::whereIn('id', $furtherIds)
            ->with('employees:id,name')
            ->get()
            ->mapWithKeys(function ($further) {
                return [
                    $further->id => $further->employees->pluck('name')->join(', '),
                ];
            });

        return view(
            'pages.general_manager.daily_activity_further.detail',
            compact(
                'costCenter',
                'psGroup',
                'line',
                'details',
                'employeeNamesByFurtherId',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function managerDetail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $line = null;

        if ($lineId) {
            $line = Line::where('department_id', $departmentId)
                ->findOrFail($lineId);
        }

        $dateFrom = $request->input(
            'date_from',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dateTo = $request->input(
            'date_to',
            now()->format('Y-m-d')
        );

        $query = DailyActivityDetailFurther::query()
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'daily_activity_detail_furthers.product_id'
            )
            ->join(
                'users',
                'users.id',
                '=',
                'daily_activity_furthers.input_by'
            )
            ->leftJoin(
                'lines',
                'lines.id',
                '=',
                'daily_activity_furthers.line_id'
            )
            ->where(
                'daily_activity_furthers.department_id',
                $departmentId
            )
            ->where(
                'daily_activity_furthers.cost_center_id',
                $costCenter->id
            )
            ->where(
                'daily_activity_furthers.ps_group_id',
                $psGroup->id
            )
            ->whereBetween(
                'daily_activity_furthers.tanggal',
                [$dateFrom, $dateTo]
            );

        if ($lineId) {
            $query->where(
                'daily_activity_furthers.line_id',
                $lineId
            );
        }

        $details = $query
            ->select(
                'daily_activity_detail_furthers.id',
                'daily_activity_furthers.id as daily_activity_further_id',
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.line_id',
                'users.name as user_name',
                'lines.name as line_name',
                'products.id as product_id',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_furthers.total_kg_rm',
                'daily_activity_detail_furthers.total_kg_fg',
                'daily_activity_detail_furthers.man_power',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.line_id')
            ->orderBy('daily_activity_detail_furthers.created_at')
            ->paginate(100)
            ->withQueryString();

        $furtherIds = $details->pluck('daily_activity_further_id')->unique();

        $employeeNamesByFurtherId = DailyActivityFurther::whereIn('id', $furtherIds)
            ->with('employees:id,name')
            ->get()
            ->mapWithKeys(function ($further) {
                return [
                    $further->id => $further->employees->pluck('name')->join(', '),
                ];
            });

        return view(
            'pages.manager.daily_activity_further.detail',
            compact(
                'costCenter',
                'psGroup',
                'line',
                'details',
                'employeeNamesByFurtherId',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $detail = DailyActivityDetailFurther::findOrFail($id);
            $dailyActivityFurtherId = $detail->daily_activity_further_id;

            $detail->delete();

            // kalau daily_activity_further sudah tidak punya detail lagi, hapus juga headernya
            $remaining = DailyActivityDetailFurther::where('daily_activity_further_id', $dailyActivityFurtherId)->count();

            if ($remaining === 0) {
                DailyActivityFurther::destroy($dailyActivityFurtherId);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Data berhasil dihapus.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        DB::beginTransaction();

        try {
            $departmentId = auth()->user()->department_id;

            $details = DailyActivityDetailFurther::with('dailyActivityFurther')
                ->whereIn('id', $request->ids)
                ->get();

            if ($details->isEmpty()) {
                throw new \Exception('Data yang dipilih tidak ditemukan.');
            }

            $parentIds = [];

            foreach ($details as $detail) {
                $parent = $detail->dailyActivityFurther;

                if (!$parent) {
                    continue;
                }

                if ((int) $parent->department_id !== (int) $departmentId) {
                    throw new \Exception('Anda tidak memiliki akses untuk menghapus data tersebut.');
                }

                $parentIds[] = $parent->id;
            }

            $parentIds = array_unique($parentIds);

            DailyActivityDetailFurther::whereIn(
                'id',
                $details->pluck('id')->toArray()
            )->delete();

            foreach ($parentIds as $parentId) {
                $remaining = DailyActivityDetailFurther::where(
                    'daily_activity_further_id',
                    $parentId
                )->count();

                if ($remaining === 0) {
                    DailyActivityFurther::where(
                        'id',
                        $parentId
                    )->delete();
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Data yang dipilih berhasil dihapus.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Gagal menghapus data: ' . $e->getMessage()
                );
        }
    }

    public function edit($id)
    {
        $detail = DailyActivityDetailFurther::with([
                'dailyActivityFurther.costCenter',
                'dailyActivityFurther.psGroup',
                'dailyActivityFurther.line',
                'dailyActivityFurther.employees',
            ])
            ->findOrFail($id);

        $productList = Product::where('cost_center_id', $detail->dailyActivityFurther->cost_center_id)
            ->orderBy('material_name')
            ->get();

        return view('pages.admin_production.daily_activity_further.edit', compact(
            'detail',
            'productList'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'total_kg_rm' => ['required', 'numeric', 'min:0'],
            'total_kg_fg' => ['required', 'numeric', 'min:0'],
        ]);

        $departmentId = auth()->user()->department_id;

        $detail = DailyActivityDetailFurther::with('dailyActivityFurther.employees')
            ->findOrFail($id);

        $dailyActivityFurther = $detail->dailyActivityFurther;

        if (!$dailyActivityFurther) {
            return redirect()
                ->back()
                ->with('error', 'Daily Activity tidak ditemukan.');
        }

        if ((int) $dailyActivityFurther->department_id !== (int) $departmentId) {
            abort(403);
        }

        // Total HK dihitung di sini, dari pivot employees, bukan lewat method di model
        $manHours = (float) $dailyActivityFurther->employees->sum('pivot.jumlah_hk');

        if ($manHours <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Data karyawan/HK pada aktivitas ini tidak valid.'
                );
        }

        DB::transaction(function () use ($request, $detail, $dailyActivityFurther, $manHours) {
            $detail->update([
                'product_id' => $request->product_id,
                'total_kg_rm' => (float) $request->total_kg_rm,
                'total_kg_fg' => (float) $request->total_kg_fg,
                'man_power' => $manHours,
            ]);

            // Recalculate total FG & productivity untuk SEMUA detail di header yang sama
            $dailyActivityFurther->load('details');

            $totalKgFg = (float) $dailyActivityFurther->details->sum('total_kg_fg');
            $productivity = $manHours > 0 ? $totalKgFg / $manHours : 0;

            foreach ($dailyActivityFurther->details as $d) {
                $d->update([
                    'man_power' => $manHours,
                    'productivity' => $productivity,
                ]);
            }
        });

        return redirect()
            ->route('admin-production.daily-activity-further.index')
            ->with('success', 'Daily Activity berhasil diperbarui.');
    }

    public function exportExcelGeneralManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');
        $lineId   = $request->line_id;

        $fileName = "daily-activity-further-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivityFurtherExport($costCenterId, $psGroupId, $fromDate, $toDate, null, $lineId),
            $fileName
        );
    }

    public function exportExcelManager(Request $request, $costCenterId, $psGroupId)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenter = CostCenter::where('department_id', $managerDepartmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $lineId = $request->line_id;

        $fileName = "daily-activity-further-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivityFurtherExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $managerDepartmentId,
                $lineId
            ),
            $fileName
        );
    }

    public function exportExcel(Request $request, $costCenterId, $psGroupId)
    {
        $adminDepartmentId = auth()->user()->department_id;

        abort_unless(
            $adminDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenter = CostCenter::where('department_id', $adminDepartmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $lineId = $request->line_id;

        $fileName = "daily-activity-further-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivityFurtherExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $adminDepartmentId,
                $lineId
            ),
            $fileName
        );
    }

    public function exportIndexExcel(Request $request)
    {
        $adminDepartmentId = auth()->user()->department_id;

        abort_unless(
            $adminDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenterId = $request->input('cost_center_id');
        $psGroupId = $request->input('ps_group_id');
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');

        $fileName = 'daily-activity-further';

        if ($fromDate && $toDate) {
            $fileName .= "-{$fromDate}-to-{$toDate}";
        }

        $fileName .= '.xlsx';

        return Excel::download(
            new DailyActivityFurtherIndexExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $adminDepartmentId
            ),
            $fileName
        );
    }

    public function exportManagerIndexExcel(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenterId = $request->input('cost_center_id');
        $psGroupId = $request->input('ps_group_id');
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');

        $fileName = 'daily-activity-further-manager';

        if ($fromDate && $toDate) {
            $fileName .= "-{$fromDate}-to-{$toDate}";
        }

        $fileName .= '.xlsx';

        return Excel::download(
            new DailyActivityFurtherIndexExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $managerDepartmentId
            ),
            $fileName
        );
    }

    public function exportGeneralManagerIndexExcel(Request $request)
    {
        $costCenterId = $request->input('cost_center_id');
        $psGroupId = $request->input('ps_group_id');
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');

        $departmentId = $request->input('department_id');

        $fileName = 'daily-activity-further-general-manager';

        if ($fromDate && $toDate) {
            $fileName .= "-{$fromDate}-to-{$toDate}";
        }

        $fileName .= '.xlsx';

        return Excel::download(
            new DailyActivityFurtherIndexExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $departmentId
            ),
            $fileName
        );
    }
    public function exportPdf(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');
        $lineId   = $request->line_id;

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetailFurther::with([
                'product',
                'dailyActivityFurther.employees',
                'dailyActivityFurther.inputBy',
                'dailyActivityFurther.line'
            ])
            ->whereHas('dailyActivityFurther', function ($q) use (
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $lineId
            ) {
                $q->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween('tanggal', [$fromDate, $toDate])
                    ->when($lineId, function ($q) use ($lineId) {
                        $q->where('line_id', $lineId);
                    });
            })
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->select('daily_activity_detail_furthers.*')
            ->get();

        $pdf = Pdf::loadView('pages.admin_production.daily_activity_further.pdf', [
            'data'          => $data,
            'fromDate'      => Carbon::parse($fromDate)->format('d M Y'),
            'toDate'        => Carbon::parse($toDate)->format('d M Y'),
            'costCenterName'=> $costCenter->name,
            'psGroupName'   => $psGroup->name,
            'lineId'        => $lineId,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-further-{$fromDate}-to-{$toDate}.pdf"
        );
    }

    public function exportPdfGeneralManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');
        $lineId   = $request->line_id;

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetailFurther::with([
                'product',
                'dailyActivityFurther.employees',
                'dailyActivityFurther.inputBy',
                'dailyActivityFurther.line'
            ])
            ->whereHas('dailyActivityFurther', function ($q) use (
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $lineId
            ) {
                $q->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween('tanggal', [$fromDate, $toDate])
                    ->when($lineId, function ($q) use ($lineId) {
                        $q->where('line_id', $lineId);
                    });
            })
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->select('daily_activity_detail_furthers.*')
            ->get();

        $pdf = Pdf::loadView('pages.general_manager.daily_activity_further.pdf', [
            'data'           => $data,
            'fromDate'       => \Carbon\Carbon::parse($fromDate)->format('d M Y'),
            'toDate'         => \Carbon\Carbon::parse($toDate)->format('d M Y'),
            'costCenterName' => $costCenter->name,
            'psGroupName'    => $psGroup->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-further-{$fromDate}-to-{$toDate}.pdf"
        );
    }

    public function exportPdfManager(Request $request, $costCenterId, $psGroupId)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');
        $lineId   = $request->line_id;

        $costCenter = CostCenter::where('department_id', $managerDepartmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $data = DailyActivityDetailFurther::with([
                'product',
                'dailyActivityFurther.employees',
                'dailyActivityFurther.inputBy',
                'dailyActivityFurther.line'
            ])
            ->whereHas('dailyActivityFurther', function ($q) use (
                $managerDepartmentId,
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $lineId
            ) {
                $q->where('department_id', $managerDepartmentId)
                    ->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween('tanggal', [$fromDate, $toDate])
                    ->when($lineId, function ($q) use ($lineId) {
                        $q->where('line_id', $lineId);
                    });
            })
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->select('daily_activity_detail_furthers.*')
            ->get();

        $pdf = Pdf::loadView('pages.manager.daily_activity_further.pdf', [
            'data'           => $data,
            'fromDate'       => \Carbon\Carbon::parse($fromDate)->format('d M Y'),
            'toDate'         => \Carbon\Carbon::parse($toDate)->format('d M Y'),
            'costCenterName' => $costCenter->name,
            'psGroupName'    => $psGroup->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-further-{$fromDate}-to-{$toDate}.pdf"
        );
    }
}