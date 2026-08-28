<?php

namespace App\Http\Controllers;

use App\Exports\DailyActivityFurtherExport;
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

        $processTypeList = ProcessType::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $lineList = Line::orderBy('name')
            ->get();

        return view(
            'pages.admin_production.daily_activity_further.create',
            compact(
                'department',
                'costCenterList',
                'lineList',
                'processTypeList'
            )
        );
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
 
            'details' => ['required', 'array', 'min:1'],
            'details.*.employee_id' => ['required', 'array', 'min:1'],
            'details.*.employee_id.*' => ['exists:employees,id'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.total_kg' => ['required', 'numeric', 'min:0'],
            'details.*.lama_packing' => ['required', 'numeric', 'min:0'],
        ]);
 
        foreach ($request->details as $detail) {
 
            $product = Product::findOrFail($detail['product_id']);
 
            $outputKg    = (float) $detail['total_kg'];
            $lamaPacking = (float) $detail['lama_packing'];
            $productivity = $lamaPacking > 0 ? $outputKg / $lamaPacking : 0;
 
            foreach ($detail['employee_id'] as $employeeId) {
 
                $dailyActivityFurther = DailyActivityFurther::firstOrCreate([
                    'employee_id' => $employeeId,
                    'tanggal' => $request->tanggal,
                    'cost_center_id' => $request->cost_center_id,
                    'ps_group_id' => $request->ps_group_id,
                    'line_id' => $request->line_id,
                    'department_id' => auth()->user()->department_id,
                    'input_by' => auth()->user()->id,
                ]);
 
                $dailyActivityFurther->details()->create([
                    'product_id'   => $product->id,
                    'total_kg'     => $outputKg,
                    'lama_packing' => $lamaPacking,
                    'productivity' => $productivity,
                ]);
            }
        }
 
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
                SUM(daily_activity_detail_furthers.total_kg) as total_kg
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
        $dateTo   = $request->input('end_date');
 
        $query = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_furthers.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_furthers.cost_center_id')
            ->join('ps_groups', 'ps_groups.id', '=', 'daily_activity_furthers.ps_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_furthers.line_id');
 
        if ($request->filled('department_id')) {
            $query->where('daily_activity_furthers.department_id', $request->department_id);
        }
 
        if ($request->filled('cost_center_id')) {
            $query->where('daily_activity_furthers.cost_center_id', $request->cost_center_id);
        }
 
        if ($request->filled('ps_group_id')) {
            $query->where('daily_activity_furthers.ps_group_id', $request->ps_group_id);
        }
 
        if ($request->filled('line_id')) {
            $query->where('daily_activity_furthers.line_id', $request->line_id);
        }
 
        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activity_furthers.tanggal', Carbon::today());
                break;
 
            case 'week':
                $query->whereBetween('daily_activity_furthers.tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
 
            case 'month':
                $query->whereMonth('daily_activity_furthers.tanggal', Carbon::now()->month)
                    ->whereYear('daily_activity_furthers.tanggal', Carbon::now()->year);
                break;
        }
 
        if ($dateFrom) {
            $query->whereDate('daily_activity_furthers.tanggal', '>=', $dateFrom);
        }
 
        if ($dateTo) {
            $query->whereDate('daily_activity_furthers.tanggal', '<=', $dateTo);
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
                SUM(daily_activity_detail_furthers.total_kg) as total_kg
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
            ->orderBy('ps_groups.id', 'ASC')
            ->get();
 
        $grandTotalKg = 0;
        $grandTotalRupiah = 0;
 
        foreach ($summaries as $summary) {
            $grandTotalKg += $summary->total_kg;
        }
        $departments = Department::orderBy('name')->get();
        $costCenters = CostCenter::orderBy('name')->get();
        $lines = Line::orderBy('name')->get();
 
        return view(
            'pages.general_manager.daily_activity_further.index',
            compact(
                'summaries',
                'departments',
                'costCenters',
                'lines',
                'grandTotalKg',
                'grandTotalRupiah',
                'dateFrom',
                'dateTo'
            )
        );
    }
 
    public function managerIndex(Request $request)
    {
        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');
 
        $managerDepartmentId = auth()->user()->department_id;
 
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');
 
        $query = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_furthers.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_furthers.cost_center_id')
            ->join('ps_groups', 'ps_groups.id', '=', 'daily_activity_furthers.ps_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_furthers.line_id')
            ->where('daily_activity_furthers.department_id', $managerDepartmentId);
 
        if ($request->filled('department_id')) {
            $query->where('daily_activity_furthers.department_id', $request->department_id);
        }
 
        if ($request->filled('cost_center_id')) {
            $query->where('daily_activity_furthers.cost_center_id', $request->cost_center_id);
        }
 
        if ($request->filled('ps_group_id')) {
            $query->where('daily_activity_furthers.ps_group_id', $request->ps_group_id);
        }
 
        if ($request->filled('line_id')) {
            $query->where('daily_activity_furthers.line_id', $request->line_id);
        }
 
        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activity_furthers.tanggal', Carbon::today());
                break;
 
            case 'week':
                $query->whereBetween('daily_activity_furthers.tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
 
            case 'month':
                $query->whereMonth('daily_activity_furthers.tanggal', Carbon::now()->month)
                    ->whereYear('daily_activity_furthers.tanggal', Carbon::now()->year);
                break;
        }
 
        if ($dateFrom) {
            $query->whereDate('daily_activity_furthers.tanggal', '>=', $dateFrom);
        }
 
        if ($dateTo) {
            $query->whereDate('daily_activity_furthers.tanggal', '<=', $dateTo);
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
                SUM(daily_activity_detail_furthers.total_kg) as total_kg
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
            ->orderBy('ps_groups.id', 'ASC')
            ->get();
 
        $grandTotalKg = 0;
        $grandTotalRupiah = 0;
 
        foreach ($summaries as $summary) {
            $grandTotalKg += $summary->total_kg;
        }
 
        $costCenters = CostCenter::orderBy('name')->get();
        $lines = Line::where('department_id', $managerDepartmentId)->orderBy('name')->get();
        $managerDepartment = Department::find($managerDepartmentId);
 
        return view(
            'pages.manager.daily_activity_further.index',
            compact(
                'summaries',
                'managerDepartment',
                'costCenters',
                'lines',
                'grandTotalKg',
                'grandTotalRupiah',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function detail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup  = PsGroup::findOrFail($psGroupId);
        $line     = $lineId ? Line::findOrFail($lineId) : null;
 
        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));
 
        $query = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('products', 'products.id', '=', 'daily_activity_detail_furthers.product_id')
            ->join('users', 'users.id', '=', 'daily_activity_furthers.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activity_furthers.employee_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_furthers.line_id')
            ->where('daily_activity_furthers.cost_center_id', $costCenterId)
            ->where('daily_activity_furthers.ps_group_id', $psGroupId)
            ->whereBetween('daily_activity_furthers.tanggal', [$dateFrom, $dateTo]);
 
        if ($lineId) {
            $query->where('daily_activity_furthers.line_id', $lineId);
        }
 
        $details = $query
            ->select(
                'daily_activity_detail_furthers.id',
                'daily_activity_furthers.tanggal',
                'users.name as user_name',
                'employees.name as employee_name',
                'lines.name as line_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_furthers.total_kg',
                'daily_activity_detail_furthers.lama_packing',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderByDesc('daily_activity_furthers.tanggal')
            ->orderByDesc('daily_activity_detail_furthers.product_id')
            ->orderByDesc('daily_activity_detail_furthers.id')
            ->paginate(100)->withQueryString();
 
        return view('pages.admin_production.daily_activity_further.detail', compact(
            'costCenter',
            'psGroup',
            'line',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }
 
    public function generalManagerDetail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup  = PsGroup::findOrFail($psGroupId);
        $line     = $lineId ? Line::findOrFail($lineId) : null;
 
        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));
 
        $query = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('products', 'products.id', '=', 'daily_activity_detail_furthers.product_id')
            ->join('users', 'users.id', '=', 'daily_activity_furthers.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activity_furthers.employee_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_furthers.line_id')
            ->where('daily_activity_furthers.cost_center_id', $costCenterId)
            ->where('daily_activity_furthers.ps_group_id', $psGroupId)
            ->whereBetween('daily_activity_furthers.tanggal', [$dateFrom, $dateTo]);
 
        if ($lineId) {
            $query->where('daily_activity_furthers.line_id', $lineId);
        }
 
        $details = $query
            ->select(
                'daily_activity_furthers.tanggal',
                'users.name as input_by',
                'employees.name as employee_name',
                'lines.name as line_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_furthers.total_kg',
                'daily_activity_detail_furthers.lama_packing',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderByDesc('daily_activity_furthers.tanggal')
            ->paginate(100)->withQueryString();
 
        return view('pages.general_manager.daily_activity_further.detail', compact(
            'costCenter',
            'psGroup',
            'line',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }
 
    public function managerDetail(Request $request, $costCenterId, $psGroupId, $lineId = null)
    {
        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');
 
        $costCenter = CostCenter::where('department_id', $managerDepartmentId)->findOrFail($costCenterId);
        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)->findOrFail($psGroupId);
        $line     = $lineId ? Line::findOrFail($lineId) : null;
 
        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));
 
        $query = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('products', 'products.id', '=', 'daily_activity_detail_furthers.product_id')
            ->join('users', 'users.id', '=', 'daily_activity_furthers.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activity_furthers.employee_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_furthers.line_id')
            ->where('daily_activity_furthers.department_id', $managerDepartmentId)
            ->where('daily_activity_furthers.cost_center_id', $costCenterId)
            ->where('daily_activity_furthers.ps_group_id', $psGroupId)
            ->whereBetween('daily_activity_furthers.tanggal', [$dateFrom, $dateTo]);
 
        if ($lineId) {
            $query->where('daily_activity_furthers.line_id', $lineId);
        }
 
        $details = $query
            ->select(
                'daily_activity_furthers.tanggal',
                'users.name as input_by',
                'employees.name as employee_name',
                'lines.name as line_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_furthers.total_kg',
                'daily_activity_detail_furthers.lama_packing',
                'daily_activity_detail_furthers.productivity'
            )
            ->orderByDesc('daily_activity_furthers.tanggal')
            ->paginate(100)->withQueryString();
 
        return view('pages.manager.daily_activity_further.detail', compact(
            'costCenter',
            'psGroup',
            'line',
            'details',
            'dateFrom',
            'dateTo',
        ));
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
 
    public function edit($id)
    {
        $detail = DailyActivityDetailFurther::with('dailyActivityFurther.costCenter', 'dailyActivityFurther.psGroup', 'dailyActivityFurther.line', 'dailyActivityFurther.employee')
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
            'product_id'    => ['required', 'exists:products,id'],
            'total_kg'     => ['required', 'numeric', 'min:0'],
            'lama_packing'  => ['required', 'numeric', 'min:0'],
        ]);
 
        DB::beginTransaction();
 
        try {
            $detail = DailyActivityDetailFurther::with('product', 'dailyActivityFurther')->findOrFail($id);
 
            $product = Product::findOrFail($request->product_id);
 
            $outputKg     = (float) $request->total_kg;
            $lamaPacking  = (float) $request->lama_packing;
            $productivity = $lamaPacking > 0 ? $outputKg / $lamaPacking : 0;
 
            $detail->update([
                'product_id'   => $product->id,
                'total_kg'     => $outputKg,
                'lama_packing' => $lamaPacking,
                'productivity' => $productivity,
            ]);
 
            $costCenterId = $detail->dailyActivityFurther->cost_center_id;
            $psGroupId    = $detail->dailyActivityFurther->ps_group_id;
            $lineId       = $detail->dailyActivityFurther->line_id;
            $dateForm     = $detail->dailyActivityFurther->tanggal->format('Y-m-d');
 
            DB::commit();
 
            return redirect()
                ->route('admin-production.daily-activity-further.detail', [
                    'costCenter' => $costCenterId,
                    'psGroup'    => $psGroupId,
                    'lineId'     => $lineId,
                    'date_from'  => $dateForm,
                    'date_to'    => $dateForm,
                ])
                ->with('success', 'Data berhasil diupdate.');
 
        } catch (\Throwable $e) {
            DB::rollBack();
 
            return redirect()
                ->back()
                ->with('error', 'Gagal mengupdate data: ' . $e->getMessage());
        }
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
 
    public function exportPdf(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');
        $lineId   = $request->line_id;

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetailFurther::with([
                'product',
                'dailyActivityFurther.employee',
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
                'dailyActivityFurther.employee',
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
                'dailyActivityFurther.employee',
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