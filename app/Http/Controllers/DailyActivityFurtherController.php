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

        $lines = Line::where('department_id', $departmentId)
            ->orderBy('name')
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
        $tanggal = $request->tanggal;
        $psGroupId = $request->ps_group_id;
        $lineId = $request->line_id;

        $attendances = Attendance::with('employee')
            ->whereDate('date', $tanggal)
            ->where('line_id', $lineId)
            ->where('status', 'hadir')
            ->whereHas('employee', function ($query) use ($psGroupId) {
                $query->where('ps_group_id', $psGroupId);
            })
            ->get();

        $employeeHk = $attendances
            ->groupBy('employee_id')
            ->map(function ($group) {
                return (float) $group->sum('jumlah_hk');
            })
            ->toArray();

        return response()->json([
            'employee_hk' => $employeeHk,
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

        ProcessType::where('department_id', $departmentId)
            ->findOrFail($request->process_type_id);

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
            'process_type_id' => ['required', 'exists:process_types,id'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.total_kg_rm' => ['required', 'numeric', 'min:0'],
            'details.*.total_kg_fg' => ['required', 'numeric', 'min:0'],
        ]);

        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenter = CostCenter::where('department_id', $departmentId)
            ->findOrFail($request->cost_center_id);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($request->ps_group_id);

        $line = Line::where('department_id', $departmentId)
            ->findOrFail($request->line_id);

        ProcessType::where('department_id', $departmentId)
            ->where('id', $request->process_type_id)
            ->findOrFail($request->process_type_id);

        $productIds = [];

        foreach ($request->details as $detail) {
            $productId = (int) $detail['product_id'];

            if (in_array($productId, $productIds)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Produk yang sama tidak boleh dimasukkan lebih dari satu kali dalam satu input.'
                    );
            }

            $productIds[] = $productId;
        }

        $existingProduct = DailyActivityDetailFurther::whereIn(
            'product_id',
            $productIds
        )
            ->whereHas('dailyActivityFurther', function ($query) use (
                $request,
                $departmentId,
                $costCenter,
                $psGroup,
                $line
            ) {
                $query->where('department_id', $departmentId)
                    ->where('cost_center_id', $costCenter->id)
                    ->where('ps_group_id', $psGroup->id)
                    ->where('line_id', $line->id)
                    ->whereDate('tanggal', $request->tanggal);
            })
            ->with('product')
            ->first();

        if ($existingProduct) {
            $productName = $existingProduct->product
                ? $existingProduct->product->material_name
                : 'tersebut';

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    "Produk {$productName} sudah digunakan pada tanggal, Cost Center, PS Group, dan Line tersebut. Silakan edit data yang sudah ada."
                );
        }

        $attendances = Attendance::with('employee')
            ->where('line_id', $line->id)
            ->whereDate('date', $request->tanggal)
            ->where('status', 'hadir')
            ->whereHas('employee', function ($query) use ($psGroup) {
                $query->where('ps_group_id', $psGroup->id);
            })
            ->get();

        if ($attendances->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Tidak ada karyawan dengan absensi hadir pada PS Group, Line, dan tanggal tersebut.'
                );
        }

        $employeeHk = [];

        foreach ($attendances as $attendance) {
            $employeeId = $attendance->employee_id;
            $jumlahHk = (float) $attendance->jumlah_hk;

            if (isset($employeeHk[$employeeId])) {
                $employeeHk[$employeeId] += $jumlahHk;
            } else {
                $employeeHk[$employeeId] = $jumlahHk;
            }
        }

        $manHours = 0;

        foreach ($employeeHk as $hk) {
            $manHours += $hk;
        }

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
            $line,
            $employeeHk,
            $manHours,
            $productivity
        ) {
            $firstEmployeeId = array_key_first($employeeHk);

            $dailyActivityFurther = DailyActivityFurther::create([
                'tanggal' => $request->tanggal,
                'department_id' => $departmentId,
                'cost_center_id' => $costCenter->id,
                'ps_group_id' => $psGroup->id,
                'line_id' => $line->id,
                'employee_id' => $firstEmployeeId,
                'input_by' => auth()->user()->id,
            ]);

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

        if (!$department) {
            abort(403, 'Akun Anda belum terhubung ke department manapun.');
        }

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
            ->where(
                'daily_activity_furthers.department_id',
                $department->id
            );

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

        $uniqueProductions = (clone $query)
            ->select(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg'
            )
            ->groupBy(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id'
            );

        $grandTotalKgRm = (float) DB::query()
            ->fromSub($uniqueProductions, 'productions')
            ->sum('total_kg_rm');

        $grandTotalKgFg = (float) DB::query()
            ->fromSub($uniqueProductions, 'productions')
            ->sum('total_kg_fg');

        $grandTotalKg = $grandTotalKgFg;

        $summaryProductions = (clone $query)
            ->select(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id',
                'departments.id as department_id',
                'departments.name as department_name',
                'cost_centers.name as cost_center_name',
                'ps_groups.name as ps_group_name',
                'lines.name as line_name'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg'
            )
            ->groupBy(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id',
                'departments.id',
                'departments.name',
                'cost_centers.name',
                'ps_groups.name',
                'lines.name'
            );

        $summaries = DB::query()
            ->fromSub($summaryProductions, 'productions')
            ->select(
                'department_id',
                'department_name',
                'cost_center_id',
                'cost_center_name',
                'ps_group_id',
                'ps_group_name',
                'line_id',
                'line_name'
            )
            ->selectRaw('SUM(total_kg_rm) as total_kg_rm')
            ->selectRaw('SUM(total_kg_fg) as total_kg_fg')
            ->groupBy(
                'department_id',
                'department_name',
                'cost_center_id',
                'cost_center_name',
                'ps_group_id',
                'ps_group_name',
                'line_id',
                'line_name'
            )
            ->orderBy('cost_center_name')
            ->orderBy('ps_group_name')
            ->orderBy('line_name')
            ->paginate(10)
            ->withQueryString();

        $costCenters = CostCenter::where(
            'department_id',
            $department->id
        )
            ->orderBy('name')
            ->get();

        $psGroups = PsGroup::whereIn(
            'cost_center_id',
            $costCenters->pluck('id')
        )
            ->orderBy('name')
            ->get();

        $lines = Line::where(
            'department_id',
            $department->id
        )
            ->orderBy('name')
            ->get();

        return view(
            'pages.admin_production.daily_activity_further.index',
            compact(
                'department',
                'costCenters',
                'psGroups',
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

        if (!$department) {
            abort(403, 'Akun Anda belum terhubung ke department manapun.');
        }

        $departmentId = $department->id;

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
            ->where(
                'daily_activity_furthers.department_id',
                $departmentId
            )
            ->where(
                'cost_centers.department_id',
                $departmentId
            );

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

        $uniqueProductions = (clone $query)
            ->select(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg'
            )
            ->groupBy(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id'
            );

        $grandTotalKgRm = (float) DB::query()
            ->fromSub($uniqueProductions, 'productions')
            ->sum('total_kg_rm');

        $grandTotalKgFg = (float) DB::query()
            ->fromSub($uniqueProductions, 'productions')
            ->sum('total_kg_fg');

        $grandTotalKg = $grandTotalKgFg;

        $summaryProductions = (clone $query)
            ->select(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id',
                'departments.id as department_id',
                'departments.name as department_name',
                'cost_centers.name as cost_center_name',
                'ps_groups.name as ps_group_name',
                'lines.name as line_name'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg'
            )
            ->groupBy(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id',
                'departments.id',
                'departments.name',
                'cost_centers.name',
                'ps_groups.name',
                'lines.name'
            );

        $summaries = DB::query()
            ->fromSub($summaryProductions, 'productions')
            ->select(
                'department_id',
                'department_name',
                'cost_center_id',
                'cost_center_name',
                'ps_group_id',
                'ps_group_name',
                'line_id',
                'line_name'
            )
            ->selectRaw('SUM(total_kg_rm) as total_kg_rm')
            ->selectRaw('SUM(total_kg_fg) as total_kg_fg')
            ->groupBy(
                'department_id',
                'department_name',
                'cost_center_id',
                'cost_center_name',
                'ps_group_id',
                'ps_group_name',
                'line_id',
                'line_name'
            )
            ->orderBy('cost_center_name')
            ->orderBy('ps_group_name')
            ->orderBy('line_name')
            ->paginate(10)
            ->withQueryString();

        $costCenters = CostCenter::where(
            'department_id',
            $departmentId
        )
            ->orderBy('name')
            ->get();

        $psGroups = PsGroup::whereIn(
            'cost_center_id',
            $costCenters->pluck('id')
        )
            ->orderBy('name')
            ->get();

        $lines = Line::where(
            'department_id',
            $departmentId
        )
            ->orderBy('name')
            ->get();

        return view(
            'pages.manager.daily_activity_further.index',
            compact(
                'department',
                'costCenters',
                'psGroups',
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

        $uniqueProductions = (clone $query)
            ->select(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg'
            )
            ->groupBy(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id'
            );

        $grandTotalKgRm = (float) DB::query()
            ->fromSub($uniqueProductions, 'productions')
            ->sum('total_kg_rm');

        $grandTotalKgFg = (float) DB::query()
            ->fromSub($uniqueProductions, 'productions')
            ->sum('total_kg_fg');

        $grandTotalKg = $grandTotalKgFg;

        $summaryProductions = (clone $query)
            ->select(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id',
                'departments.id as department_id',
                'departments.name as department_name',
                'cost_centers.name as cost_center_name',
                'ps_groups.name as ps_group_name',
                'lines.name as line_name'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_rm) as total_kg_rm'
            )
            ->selectRaw(
                'MAX(daily_activity_detail_furthers.total_kg_fg) as total_kg_fg'
            )
            ->groupBy(
                'daily_activity_furthers.tanggal',
                'daily_activity_furthers.cost_center_id',
                'daily_activity_furthers.ps_group_id',
                'daily_activity_furthers.line_id',
                'daily_activity_detail_furthers.product_id',
                'departments.id',
                'departments.name',
                'cost_centers.name',
                'ps_groups.name',
                'lines.name'
            );

        $summaries = DB::query()
            ->fromSub($summaryProductions, 'productions')
            ->select(
                'department_id',
                'department_name',
                'cost_center_id',
                'cost_center_name',
                'ps_group_id',
                'ps_group_name',
                'line_id',
                'line_name'
            )
            ->selectRaw('SUM(total_kg_rm) as total_kg_rm')
            ->selectRaw('SUM(total_kg_fg) as total_kg_fg')
            ->groupBy(
                'department_id',
                'department_name',
                'cost_center_id',
                'cost_center_name',
                'ps_group_id',
                'ps_group_name',
                'line_id',
                'line_name'
            )
            ->orderBy('department_name')
            ->orderBy('cost_center_name')
            ->orderBy('ps_group_name')
            ->orderBy('line_name')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        if ($request->filled('department_id')) {
            $costCenters = CostCenter::where(
                'department_id',
                $request->department_id
            )
                ->orderBy('name')
                ->get();

            $lines = Line::where(
                'department_id',
                $request->department_id
            )
                ->orderBy('name')
                ->get();
        } else {
            $costCenters = CostCenter::orderBy('name')->get();
            $lines = Line::orderBy('name')->get();
        }

        $psGroups = PsGroup::orderBy('name')->get();

        return view(
            'pages.general_manager.daily_activity_further.index',
            compact(
                'departments',
                'costCenters',
                'psGroups',
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

    public function detail(
        Request $request,
        $costCenterId,
        $psGroupId,
        $lineId = null
    ) {
        $departmentId = auth()->user()->department_id;

        if (!$departmentId) {
            abort(403, 'Akun Anda belum terhubung ke department manapun.');
        }

        $costCenter = CostCenter::where(
            'department_id',
            $departmentId
        )->findOrFail($costCenterId);

        $psGroup = PsGroup::where(
            'cost_center_id',
            $costCenter->id
        )->findOrFail($psGroupId);

        $line = null;

        if ($lineId) {
            $line = Line::where(
                'department_id',
                $departmentId
            )->findOrFail($lineId);
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
                'employees',
                'employees.id',
                '=',
                'daily_activity_furthers.employee_id'
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
                'employees.id as employee_id',
                'employees.name as employee_name',
                'products.id as product_id',
                'products.material_code',
                'products.material_name',
                'daily_activity_furthers.employee_id',
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

        return view(
            'pages.admin_production.daily_activity_further.detail',
            compact(
                'costCenter',
                'psGroup',
                'line',
                'details',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function managerDetail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $departmentId = auth()->user()->department_id;

        if (!$departmentId) {
            abort(403, 'Akun Anda belum terhubung ke department manapun.');
        }

        $costCenter = CostCenter::where(
            'department_id',
            $departmentId
        )->findOrFail($costCenterId);

        $psGroup = PsGroup::where(
            'cost_center_id',
            $costCenter->id
        )->findOrFail($psGroupId);

        $line = null;

        if ($lineId) {
            $line = Line::where(
                'department_id',
                $departmentId
            )->findOrFail($lineId);
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
                'employees',
                'employees.id',
                '=',
                'daily_activity_furthers.employee_id'
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
                'employees.id as employee_id',
                'employees.name as employee_name',
                'products.id as product_id',
                'products.material_code',
                'products.material_name',
                'daily_activity_furthers.employee_id',
                'daily_activity_detail_furthers.total_kg_rm',
                'daily_activity_detail_furthers.total_kg_fg',
                'daily_activity_detail_furthers.man_power',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.line_id')
            ->orderBy('daily_activity_detail_furthers.created_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.manager.daily_activity_further.detail',
            compact(
                'costCenter',
                'psGroup',
                'line',
                'details',
                'dateFrom',
                'dateTo'
            )
        );
    }
    
    public function generalManagerDetail(Request $request, $costCenterId, $psGroupId, $lineId = null) 
    {
        $costCenter = CostCenter::findOrFail($costCenterId);

        $psGroup = PsGroup::where(
            'cost_center_id',
            $costCenter->id
        )->findOrFail($psGroupId);

        $line = null;

        if ($lineId) {
            $line = Line::where(
                'department_id',
                $costCenter->department_id
            )->findOrFail($lineId);
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
                'employees',
                'employees.id',
                '=',
                'daily_activity_furthers.employee_id'
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
                'employees.id as employee_id',
                'employees.name as employee_name',
                'products.id as product_id',
                'products.material_code',
                'products.material_name',
                'daily_activity_furthers.employee_id',
                'daily_activity_detail_furthers.total_kg_rm',
                'daily_activity_detail_furthers.total_kg_fg',
                'daily_activity_detail_furthers.man_power',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.line_id')
            ->orderBy('daily_activity_detail_furthers.created_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.general_manager.daily_activity_further.detail',
            compact(
                'costCenter',
                'psGroup',
                'line',
                'details',
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

            $remaining = DailyActivityDetailFurther::where(
                'daily_activity_further_id',
                $dailyActivityFurtherId
            )->count();

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
                ->with(
                    'error',
                    'Gagal menghapus data: ' . $e->getMessage()
                );
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

            $details = DailyActivityDetailFurther::with(
                'dailyActivityFurther'
            )
                ->whereIn('id', $request->ids)
                ->get();

            if ($details->isEmpty()) {
                throw new \Exception(
                    'Data yang dipilih tidak ditemukan.'
                );
            }

            $parentIds = [];

            foreach ($details as $detail) {
                $parent = $detail->dailyActivityFurther;

                if (!$parent) {
                    continue;
                }

                if (
                    (int) $parent->department_id !==
                    (int) $departmentId
                ) {
                    throw new \Exception(
                        'Anda tidak memiliki akses untuk menghapus data tersebut.'
                    );
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
        $departmentId = auth()->user()->department_id;

        if (!$departmentId) {
            abort(
                403,
                'Akun Anda belum terhubung ke department manapun.'
            );
        }

        $detail = DailyActivityDetailFurther::with([
            'dailyActivityFurther.costCenter',
            'dailyActivityFurther.psGroup',
            'dailyActivityFurther.line',
            'dailyActivityFurther.employee',
            'product',
        ])->findOrFail($id);

        $dailyActivityFurther = $detail->dailyActivityFurther;

        if (!$dailyActivityFurther) {
            abort(404, 'Daily Activity tidak ditemukan.');
        }

        if (
            (int) $dailyActivityFurther->department_id !==
            (int) $departmentId
        ) {
            abort(403);
        }

        $productList = Product::where(
            'cost_center_id',
            $dailyActivityFurther->cost_center_id
        )
            ->orderBy('material_name')
            ->get([
                'id',
                'material_name',
                'material_code',
            ]);

        return view(
            'pages.admin_production.daily_activity_further.edit',
            compact(
                'detail',
                'productList'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'total_kg_rm' => ['required', 'numeric', 'min:0'],
            'total_kg_fg' => ['required', 'numeric', 'min:0'],
        ]);

        $departmentId = auth()->user()->department_id;

        if (!$departmentId) {
            abort(
                403,
                'Akun Anda belum terhubung ke department manapun.'
            );
        }

        $detail = DailyActivityDetailFurther::with(
            'dailyActivityFurther'
        )->findOrFail($id);

        $dailyActivityFurther = $detail->dailyActivityFurther;

        if (!$dailyActivityFurther) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Daily Activity tidak ditemukan.'
                );
        }

        if (
            (int) $dailyActivityFurther->department_id !==
            (int) $departmentId
        ) {
            abort(403);
        }

        $existingProduct = DailyActivityDetailFurther::where(
            'product_id',
            $request->product_id
        )
            ->where('id', '!=', $detail->id)
            ->whereHas('dailyActivityFurther', function ($query) use (
                $dailyActivityFurther
            ) {
                $query->where(
                    'department_id',
                    $dailyActivityFurther->department_id
                )
                    ->where(
                        'cost_center_id',
                        $dailyActivityFurther->cost_center_id
                    )
                    ->where(
                        'ps_group_id',
                        $dailyActivityFurther->ps_group_id
                    )
                    ->where(
                        'line_id',
                        $dailyActivityFurther->line_id
                    )
                    ->whereDate(
                        'tanggal',
                        $dailyActivityFurther->tanggal
                    );
            })
            ->with('product')
            ->first();

        if ($existingProduct) {
            $productName = $existingProduct->product
                ? $existingProduct->product->material_name
                : 'tersebut';

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    "Produk {$productName} sudah digunakan pada tanggal, Cost Center, PS Group, dan Line tersebut. Silakan gunakan produk lain."
                );
        }

        $attendances = Attendance::with('employee')
            ->where('line_id', $dailyActivityFurther->line_id)
            ->whereDate(
                'date',
                $dailyActivityFurther->tanggal
            )
            ->where('status', 'hadir')
            ->whereHas('employee', function ($query) use (
                $dailyActivityFurther
            ) {
                $query->where(
                    'ps_group_id',
                    $dailyActivityFurther->ps_group_id
                );
            })
            ->get();

        if ($attendances->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Tidak ada karyawan dengan absensi hadir pada PS Group, Line, dan tanggal tersebut.'
                );
        }

        $employeeHk = [];

        foreach ($attendances as $attendance) {
            $employeeId = $attendance->employee_id;
            $jumlahHk = (float) $attendance->jumlah_hk;

            if (isset($employeeHk[$employeeId])) {
                $employeeHk[$employeeId] += $jumlahHk;
            } else {
                $employeeHk[$employeeId] = $jumlahHk;
            }
        }

        $manHours = 0;

        foreach ($employeeHk as $hk) {
            $manHours += $hk;
        }

        if ($manHours <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Total Man Hours harus lebih dari 0.'
                );
        }

        DB::transaction(function () use (
            $request,
            $detail,
            $dailyActivityFurther,
            $manHours
        ) {
            $detail->update([
                'product_id' => $request->product_id,
                'total_kg_rm' => (float) $request->total_kg_rm,
                'total_kg_fg' => (float) $request->total_kg_fg,
                'man_power' => $manHours,
            ]);

            $details = DailyActivityDetailFurther::where(
                'daily_activity_further_id',
                $dailyActivityFurther->id
            )->get();

            $totalKgFg = 0;

            foreach ($details as $item) {
                $totalKgFg += (float) $item->total_kg_fg;
            }

            $productivity = $totalKgFg / $manHours;

            foreach ($details as $item) {
                $item->update([
                    'man_power' => $manHours,
                    'productivity' => $productivity,
                ]);
            }
        });

        return redirect()
            ->route(
                'admin-production.daily-activity-further.index'
            )
            ->with(
                'success',
                'Daily Activity berhasil diperbarui.'
            );
    }

    public function exportExcelManager(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $lineId = $request->line_id;

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

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

    public function exportExcelGeneralManager(Request $request)
    {
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $lineId = $request->line_id;

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $fileName = "daily-activity-further-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivityFurtherExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                null,
                $lineId
            ),
            $fileName
        );
    }

    public function exportExcel(Request $request)
    {
        $adminDepartmentId = auth()->user()->department_id;

        abort_unless(
            $adminDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $lineId = $request->line_id;

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

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

        $fromDate = $request->input('start_date')
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->input('end_date')
            ?? now()->format('Y-m-d');

        $fileName = "daily-activity-further-{$fromDate}-to-{$toDate}.xlsx";

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

        $fromDate = $request->input('start_date')
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->input('end_date')
            ?? now()->format('Y-m-d');

        $fileName = "daily-activity-further-manager-{$fromDate}-to-{$toDate}.xlsx";

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

        $fromDate = $request->input('start_date')
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->input('end_date')
            ?? now()->format('Y-m-d');

        $departmentId = $request->input('department_id');

        $fileName = "daily-activity-further-general-manager-{$fromDate}-to-{$toDate}.xlsx";

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

    public function exportPdf(
        Request $request,
        $costCenterId,
        $psGroupId
    ) {
        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $lineId = $request->line_id;

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup = PsGroup::findOrFail($psGroupId);

        $query = DailyActivityDetailFurther::with([
            'product',
            'dailyActivityFurther.employee',
            'dailyActivityFurther.inputBy',
            'dailyActivityFurther.line'
        ])
            ->whereHas(
                'dailyActivityFurther',
                function ($q) use (
                    $costCenterId,
                    $psGroupId,
                    $fromDate,
                    $toDate,
                    $lineId
                ) {
                    $q->where(
                        'cost_center_id',
                        $costCenterId
                    )
                        ->where(
                            'ps_group_id',
                            $psGroupId
                        )
                        ->whereBetween(
                            'tanggal',
                            [$fromDate, $toDate]
                        );

                    if ($lineId) {
                        $q->where(
                            'line_id',
                            $lineId
                        );
                    }
                }
            )
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy(
                'daily_activity_furthers.tanggal'
            )
            ->select(
                'daily_activity_detail_furthers.*'
            )
            ->get();

        $pdf = Pdf::loadView(
            'pages.admin_production.daily_activity_further.pdf',
            [
                'data' => $query,
                'fromDate' => Carbon::parse($fromDate)->format('d M Y'),
                'toDate' => Carbon::parse($toDate)->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
                'lineId' => $lineId,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-further-{$fromDate}-to-{$toDate}.pdf"
        );
    }

    public function exportPdfGeneralManager(
        Request $request,
        $costCenterId,
        $psGroupId
    ) {
        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $lineId = $request->line_id;

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup = PsGroup::findOrFail($psGroupId);

        $query = DailyActivityDetailFurther::with([
            'product',
            'dailyActivityFurther.employee',
            'dailyActivityFurther.inputBy',
            'dailyActivityFurther.line'
        ])
            ->whereHas(
                'dailyActivityFurther',
                function ($q) use (
                    $costCenterId,
                    $psGroupId,
                    $fromDate,
                    $toDate,
                    $lineId
                ) {
                    $q->where(
                        'cost_center_id',
                        $costCenterId
                    )
                        ->where(
                            'ps_group_id',
                            $psGroupId
                        )
                        ->whereBetween(
                            'tanggal',
                            [$fromDate, $toDate]
                        );

                    if ($lineId) {
                        $q->where(
                            'line_id',
                            $lineId
                        );
                    }
                }
            )
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy(
                'daily_activity_furthers.tanggal'
            )
            ->select(
                'daily_activity_detail_furthers.*'
            )
            ->get();

        $pdf = Pdf::loadView(
            'pages.general_manager.daily_activity_further.pdf',
            [
                'data' => $query,
                'fromDate' => Carbon::parse($fromDate)->format('d M Y'),
                'toDate' => Carbon::parse($toDate)->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-further-{$fromDate}-to-{$toDate}.pdf"
        );
    }

    public function exportPdfManager(
        Request $request,
        $costCenterId,
        $psGroupId
    ) {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $lineId = $request->line_id;

        $costCenter = CostCenter::where(
            'department_id',
            $managerDepartmentId
        )->findOrFail($costCenterId);

        $psGroup = PsGroup::where(
            'cost_center_id',
            $costCenter->id
        )->findOrFail($psGroupId);

        $query = DailyActivityDetailFurther::with([
            'product',
            'dailyActivityFurther.employee',
            'dailyActivityFurther.inputBy',
            'dailyActivityFurther.line'
        ])
            ->whereHas(
                'dailyActivityFurther',
                function ($q) use (
                    $managerDepartmentId,
                    $costCenterId,
                    $psGroupId,
                    $fromDate,
                    $toDate,
                    $lineId
                ) {
                    $q->where(
                        'department_id',
                        $managerDepartmentId
                    )
                        ->where(
                            'cost_center_id',
                            $costCenterId
                        )
                        ->where(
                            'ps_group_id',
                            $psGroupId
                        )
                        ->whereBetween(
                            'tanggal',
                            [$fromDate, $toDate]
                        );

                    if ($lineId) {
                        $q->where(
                            'line_id',
                            $lineId
                        );
                    }
                }
            )
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->orderBy(
                'daily_activity_furthers.tanggal'
            )
            ->select(
                'daily_activity_detail_furthers.*'
            )
            ->get();

        $pdf = Pdf::loadView(
            'pages.manager.daily_activity_further.pdf',
            [
                'data' => $query,
                'fromDate' => Carbon::parse($fromDate)->format('d M Y'),
                'toDate' => Carbon::parse($toDate)->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-further-{$fromDate}-to-{$toDate}.pdf"
        );
    }
}