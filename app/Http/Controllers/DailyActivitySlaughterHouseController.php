<?php

namespace App\Http\Controllers;

use App\Exports\DailyActivitySlaughterHouseExport;
use App\Exports\PenggajianBoronganExport;
use App\Models\CostCenter;
use App\Models\DailyActivityDetailSlaughterHouse;
use App\Models\DailyActivitySlaughterHouse;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Line;
use App\Models\PenggajianBorongan;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\PsGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DailyActivitySlaughterHouseController extends Controller
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

        $lineList = Line::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $employeeList = Employee::where('department_id', $departmentId)
            ->where('employee_status', 'borongan')
            ->orderBy('name')
            ->get(['id', 'nik', 'name', 'employee_status', 'outsourcing_id']);

        $productGroupList = ProductGroup::where('department_id', $departmentId)->orderBy('name')
            ->get();

        return view(
            'pages.admin_production.daily_activity_slaughter_house.create',
            compact(
                'department',
                'costCenterList',
                'lineList',
                'employeeList',
                'productGroupList'
            )
        );
    }
 
    public function getCostCenters($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);
 
        return response()->json($costCenters);
    }

    public function getPsGroups($costCenterId)
    {
        $groups = PsGroup::where('cost_center_id', $costCenterId)
            ->orderBy('name')
            ->get(['id', 'name']);
 
        return response()->json($groups);
    }
 
    public function getProducts($costCenterId)
    {
        $products = Product::where('cost_center_id', $costCenterId)
            ->orderBy('material_name')
            ->get([
                'id',
                'material_name',
                'material_code',
                'harga_per_kg',
            ]);
 
        return response()->json($products);
    }
 
    public function getLines($departmentId)
    {
        $lines = Line::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
 
        return response()->json($lines);
    }

    public function getEmployees()
    {
        $employees = Employee::where('employee_status', 'borongan')
            ->orderBy('name')
            ->get(['id', 'nik', 'name', 'employee_status']);
 
        return response()->json($employees);
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
            'details.*.output_kg' => ['required', 'numeric', 'min:0'],
            'details.*.lama_packing' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $tanggal = Carbon::parse($request->tanggal);
            $departmentId = auth()->user()->department_id;
            $userId = auth()->user()->id;

            foreach ($request->details as $detail) {
                $product = Product::findOrFail($detail['product_id']);

                $outputKg = (float) $detail['output_kg'];
                $lamaPacking = (float) $detail['lama_packing'];
                $hargaPerKg = (float) $product->harga_per_kg;

                $totalHarga = $outputKg * $hargaPerKg;

                $productivity = $lamaPacking > 0
                    ? $outputKg / $lamaPacking
                    : 0;

                foreach ($detail['employee_id'] as $employeeId) {

                    $dailyActivitySlaughterHouse = DailyActivitySlaughterHouse::firstOrCreate(
                        [
                            'employee_id' => $employeeId,
                            'tanggal' => $tanggal->format('Y-m-d'),
                            'cost_center_id' => $request->cost_center_id,
                            'ps_group_id' => $request->ps_group_id,
                            'product_group_id' => $product->product_group_id ?? null,
                            'line_id' => $request->line_id,
                        ],
                        [
                            'department_id' => $departmentId,
                            'input_by' => $userId,
                        ]
                    );

                    $dailyActivitySlaughterHouse->details()->create([
                        'product_id' => $product->id,
                        'total_kg' => $outputKg,
                        'harga_per_kg' => $hargaPerKg,
                        'total_harga' => $totalHarga,
                        'lama_packing' => $lamaPacking,
                        'productivity' => $productivity,
                    ]);

                    $payroll = PenggajianBorongan::firstOrCreate(
                        [
                            'employee_id' => $employeeId,
                            'period_month' => $tanggal->month,
                            'period_year' => $tanggal->year,
                        ],
                        [
                            'total_kg' => 0,
                            'total_hari_kerja' => 0,
                            'total_upah' => 0,
                            'jamsostek' => 0,
                            'bpjs_kesehatan' => 0,
                            'bpjs_pensiun' => 0,
                            'managemen_fee' => 0,
                            'grand_total_upah' => 0,
                        ]
                    );

                    $monthlyDetails = DailyActivityDetailSlaughterHouse::query()
                        ->join(
                            'daily_activity_slaughter_houses',
                            'daily_activity_slaughter_houses.id',
                            '=',
                            'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                        )
                        ->where(
                            'daily_activity_slaughter_houses.employee_id',
                            $employeeId
                        )
                        ->whereMonth(
                            'daily_activity_slaughter_houses.tanggal',
                            $tanggal->month
                        )
                        ->whereYear(
                            'daily_activity_slaughter_houses.tanggal',
                            $tanggal->year
                        )
                        ->select(
                            'daily_activity_detail_slaughter_houses.total_kg',
                            'daily_activity_detail_slaughter_houses.total_harga'
                        )
                        ->get();

                    $totalKg = $monthlyDetails->sum('total_kg');
                    $totalUpah = $monthlyDetails->sum('total_harga');

                    $totalHariKerja = DailyActivitySlaughterHouse::where(
                        'employee_id',
                        $employeeId
                    )
                        ->whereMonth('tanggal', $tanggal->month)
                        ->whereYear('tanggal', $tanggal->year)
                        ->distinct()
                        ->count('tanggal');

                    $jamsostek = round($totalUpah * 0.0489, 2);
                    $bpjsKesehatan = round($totalUpah * 0.04, 2);
                    $bpjsPensiun = round($totalUpah * 0.02, 2);

                    $managemenFeePerDay = 175000 / 25;
                    $managemenFee = $totalHariKerja * $managemenFeePerDay;

                    $grandTotalUpah =
                        $totalUpah
                        + $jamsostek
                        + $bpjsKesehatan
                        + $bpjsPensiun
                        + $managemenFee;

                    $payroll->update([
                        'total_kg' => $totalKg,
                        'total_hari_kerja' => $totalHariKerja,
                        'total_upah' => $totalUpah,
                        'jamsostek' => $jamsostek,
                        'bpjs_kesehatan' => $bpjsKesehatan,
                        'bpjs_pensiun' => $bpjsPensiun,
                        'managemen_fee' => $managemenFee,
                        'grand_total_upah' => $grandTotalUpah,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin-production.daily-activity-slaughter-house.index')
                ->with(
                    'success',
                    'Daily Activity Slaughter House berhasil disimpan.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan data: ' . $e->getMessage()
                );
        }
    }

    public function index(Request $request)
    {
        $department = auth()->user()->department;
 
        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');
 
        $query = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_slaughter_houses.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_slaughter_houses.cost_center_id')
            ->leftJoin('ps_groups', 'ps_groups.id', '=', 'daily_activity_slaughter_houses.ps_group_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'daily_activity_slaughter_houses.product_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_slaughter_houses.line_id')
            ->where('cost_centers.department_id', $department->id);
 
        if ($request->filled('cost_center_id')) {
            $query->where('daily_activity_slaughter_houses.cost_center_id', $request->cost_center_id);
        }
 
        if ($request->filled('ps_group_id')) {
            $query->where('daily_activity_slaughter_houses.ps_group_id', $request->ps_group_id);
        }
 
        if ($request->filled('product_group_id')) {
            $query->where('daily_activity_slaughter_houses.product_group_id', $request->product_group_id);
        }
 
        if ($request->filled('line_id')) {
            $query->where('daily_activity_slaughter_houses.line_id', $request->line_id);
        }
 
        if ($dateFrom) {
            $query->whereDate('daily_activity_slaughter_houses.tanggal', '>=', $dateFrom);
        }
 
        if ($dateTo) {
            $query->whereDate('daily_activity_slaughter_houses.tanggal', '<=', $dateTo);
        }
 
        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activity_slaughter_houses.tanggal', today());
                break;
 
            case 'week':
                $query->whereBetween('daily_activity_slaughter_houses.tanggal', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;
 
            case 'month':
                $query->whereMonth('daily_activity_slaughter_houses.tanggal', now()->month)
                    ->whereYear('daily_activity_slaughter_houses.tanggal', now()->year);
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
                product_groups.id as product_group_id,
                product_groups.name as product_group_name,
                lines.id as line_id,
                lines.name as line_name,
                SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name',
                'product_groups.id',
                'product_groups.name',
                'lines.id',
                'lines.name'
            )
            ->orderBy('cost_centers.name')
            ->orderBy('product_groups.name')
            ->paginate(50);
 
        $grandTotalKg = 0;
        $grandTotalRupiah = 0;
 
        foreach ($summaries as $summary) {
            $summary->harga_per_kg = $summary->total_kg > 0
                ? $summary->total_rupiah / $summary->total_kg
                : 0;
 
            $grandTotalKg += $summary->total_kg;
            $grandTotalRupiah += $summary->total_rupiah;
        }
 
        $grandHargaPerKg = $grandTotalKg > 0
            ? $grandTotalRupiah / $grandTotalKg
            : 0;
 
        $costCenters = CostCenter::where('department_id', $department->id)
            ->orderBy('name')
            ->get();
 
        $lines = Line::where('department_id', $department->id)
            ->orderBy('name')
            ->get();
 
        return view('pages.admin_production.daily_activity_slaughter_house.index', compact(
            'department',
            'costCenters',
            'lines',
            'summaries',
            'grandTotalKg',
            'grandTotalRupiah',
            'grandHargaPerKg',
            'dateFrom',
            'dateTo'
        ));
    }
 
    public function generalManagerIndex(Request $request)
    {
        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');
 
        $query = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_slaughter_houses.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_slaughter_houses.cost_center_id')
            ->join('ps_groups', 'ps_groups.id', '=', 'daily_activity_slaughter_houses.ps_group_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'daily_activity_slaughter_houses.product_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_slaughter_houses.line_id');
 
        if ($request->filled('department_id')) {
            $query->where('daily_activity_slaughter_houses.department_id', $request->department_id);
        }
 
        if ($request->filled('cost_center_id')) {
            $query->where('daily_activity_slaughter_houses.cost_center_id', $request->cost_center_id);
        }
 
        if ($request->filled('ps_group_id')) {
            $query->where('daily_activity_slaughter_houses.ps_group_id', $request->ps_group_id);
        }
 
        if ($request->filled('product_group_id')) {
            $query->where('daily_activity_slaughter_houses.product_group_id', $request->product_group_id);
        }
 
        if ($request->filled('line_id')) {
            $query->where('daily_activity_slaughter_houses.line_id', $request->line_id);
        }
 
        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activity_slaughter_houses.tanggal', Carbon::today());
                break;
 
            case 'week':
                $query->whereBetween('daily_activity_slaughter_houses.tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
 
            case 'month':
                $query->whereMonth('daily_activity_slaughter_houses.tanggal', Carbon::now()->month)
                    ->whereYear('daily_activity_slaughter_houses.tanggal', Carbon::now()->year);
                break;
        }
 
        if ($dateFrom) {
            $query->whereDate('daily_activity_slaughter_houses.tanggal', '>=', $dateFrom);
        }
 
        if ($dateTo) {
            $query->whereDate('daily_activity_slaughter_houses.tanggal', '<=', $dateTo);
        }
 
        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                product_groups.id as product_group_id,
                product_groups.name as product_group_name,
                lines.id as line_id,
                lines.name as line_name,
                SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name',
                'product_groups.id',
                'product_groups.name',
                'lines.id',
                'lines.name'
            )
            ->orderBy('departments.name')
            ->orderBy('cost_centers.name')
            ->get();
 
        $grandTotalKg = 0;
        $grandTotalRupiah = 0;
 
        foreach ($summaries as $summary) {
            $summary->harga_per_kg = $summary->total_kg > 0
                ? $summary->total_rupiah / $summary->total_kg
                : 0;
 
            $grandTotalKg += $summary->total_kg;
            $grandTotalRupiah += $summary->total_rupiah;
        }
 
        $grandHargaPerKg = $grandTotalKg > 0
            ? $grandTotalRupiah / $grandTotalKg
            : 0;
 
        $departments = Department::orderBy('name')->get();
        $costCenters = CostCenter::orderBy('name')->get();
        $lines = Line::orderBy('name')->get();
 
        return view(
            'pages.general_manager.daily_activity_slaughter_house.index',
            compact(
                'summaries',
                'departments',
                'costCenters',
                'lines',
                'grandTotalKg',
                'grandTotalRupiah',
                'grandHargaPerKg',
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
 
        $query = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_slaughter_houses.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_slaughter_houses.cost_center_id')
            ->join('ps_groups', 'ps_groups.id', '=', 'daily_activity_slaughter_houses.ps_group_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'daily_activity_slaughter_houses.product_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_slaughter_houses.line_id')
            ->where('daily_activity_slaughter_houses.department_id', $managerDepartmentId);
 
        if ($request->filled('cost_center_id')) {
            $query->where('daily_activity_slaughter_houses.cost_center_id', $request->cost_center_id);
        }
 
        if ($request->filled('ps_group_id')) {
            $query->where('daily_activity_slaughter_houses.ps_group_id', $request->ps_group_id);
        }
 
        if ($request->filled('product_group_id')) {
            $query->where('daily_activity_slaughter_houses.product_group_id', $request->product_group_id);
        }
 
        if ($request->filled('line_id')) {
            $query->where('daily_activity_slaughter_houses.line_id', $request->line_id);
        }
 
        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activity_slaughter_houses.tanggal', Carbon::today());
                break;
 
            case 'week':
                $query->whereBetween('daily_activity_slaughter_houses.tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
 
            case 'month':
                $query->whereMonth('daily_activity_slaughter_houses.tanggal', Carbon::now()->month)
                    ->whereYear('daily_activity_slaughter_houses.tanggal', Carbon::now()->year);
                break;
        }
 
        if ($dateFrom) {
            $query->whereDate('daily_activity_slaughter_houses.tanggal', '>=', $dateFrom);
        }
 
        if ($dateTo) {
            $query->whereDate('daily_activity_slaughter_houses.tanggal', '<=', $dateTo);
        }
 
        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                product_groups.id as product_group_id,
                product_groups.name as product_group_name,
                lines.id as line_id,
                lines.name as line_name,
                SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name',
                'product_groups.id',
                'product_groups.name',
                'lines.id',
                'lines.name'
            )
            ->orderBy('cost_centers.name')
            ->get();
 
        $grandTotalKg = 0;
        $grandTotalRupiah = 0;
 
        foreach ($summaries as $summary) {
            $summary->harga_per_kg = $summary->total_kg > 0
                ? $summary->total_rupiah / $summary->total_kg
                : 0;
 
            $grandTotalKg += $summary->total_kg;
            $grandTotalRupiah += $summary->total_rupiah;
        }
 
        $grandHargaPerKg = $grandTotalKg > 0
            ? $grandTotalRupiah / $grandTotalKg
            : 0;
 
        $costCenters = CostCenter::orderBy('name')->get();
        // $lines = Line::where('department_id', $managerDepartmentId)->orderBy('name')->get();
        $managerDepartment = Department::find($managerDepartmentId);
 
        return view(
            'pages.manager.daily_activity_slaughter_house.index',
            compact(
                'summaries',
                'managerDepartment',
                'costCenters',
                'grandTotalKg',
                'grandTotalRupiah',
                'grandHargaPerKg',
                'dateFrom',
                'dateTo'
            )
        );
    }
 
    public function detail(Request $request, $costCenterId, $psGroupId, $lineId)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);
        $line = Line::findOrFail($lineId);
 
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', now()->format('Y-m-d'));
 
        $details = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->join('products', 'products.id', '=', 'daily_activity_detail_slaughter_houses.product_id')
            ->join('users', 'users.id', '=', 'daily_activity_slaughter_houses.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activity_slaughter_houses.employee_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'daily_activity_slaughter_houses.product_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_slaughter_houses.line_id')
            ->where('daily_activity_slaughter_houses.cost_center_id', $costCenterId)
            ->where('daily_activity_slaughter_houses.ps_group_id', $psGroupId)
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_activity_detail_slaughter_houses.id',
                'daily_activity_slaughter_houses.tanggal',
                'users.name as user_name',
                'employees.name as employee_name',
                'product_groups.name as product_group_name',
                'lines.name as line_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_slaughter_houses.total_kg',
                'daily_activity_detail_slaughter_houses.harga_per_kg',
                'daily_activity_detail_slaughter_houses.total_harga',
                'daily_activity_detail_slaughter_houses.lama_packing',
                'daily_activity_detail_slaughter_houses.productivity'
            )
            ->orderByDesc('daily_activity_slaughter_houses.tanggal')
            ->orderByDesc('daily_activity_detail_slaughter_houses.id')
            ->paginate(50)->withQueryString();
 
        return view('pages.admin_production.daily_activity_slaughter_house.detail', compact(
            'costCenter',
            'psGroup',
            'line',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }
 
    public function generalManagerDetail(Request $request, $costCenterId, $psGroupId)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);
 
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', now()->format('Y-m-d'));
 
        $details = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->join('products', 'products.id', '=', 'daily_activity_detail_slaughter_houses.product_id')
            ->join('users', 'users.id', '=', 'daily_activity_slaughter_houses.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activity_slaughter_houses.employee_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'daily_activity_slaughter_houses.product_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_slaughter_houses.line_id')
            ->where('daily_activity_slaughter_houses.cost_center_id', $costCenterId)
            ->where('daily_activity_slaughter_houses.ps_group_id', $psGroupId)
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_activity_slaughter_houses.tanggal',
                'users.name as input_by',
                'employees.name as employee_name',
                'product_groups.name as product_group_name',
                'lines.name as line_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_slaughter_houses.total_kg',
                'daily_activity_detail_slaughter_houses.harga_per_kg',
                'daily_activity_detail_slaughter_houses.total_harga',
                'daily_activity_detail_slaughter_houses.lama_packing',
                'daily_activity_detail_slaughter_houses.productivity'
            )
            ->orderByDesc('daily_activity_slaughter_houses.tanggal')
            ->paginate(50)->withQueryString();
 
        return view('pages.general_manager.daily_activity_slaughter_house.detail', compact(
            'costCenter',
            'psGroup',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }
 
    public function managerDetail(Request $request, $costCenterId, $psGroupId)
    {
        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');
 
        $costCenter = CostCenter::where('department_id', $managerDepartmentId)->findOrFail($costCenterId);
        $psGroup    = PsGroup::where('cost_center_id', $costCenter->id)->findOrFail($psGroupId);
 
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', now()->format('Y-m-d'));
 
        $details = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->join('products', 'products.id', '=', 'daily_activity_detail_slaughter_houses.product_id')
            ->join('users', 'users.id', '=', 'daily_activity_slaughter_houses.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activity_slaughter_houses.employee_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'daily_activity_slaughter_houses.product_group_id')
            ->leftJoin('lines', 'lines.id', '=', 'daily_activity_slaughter_houses.line_id')
            ->where('daily_activity_slaughter_houses.department_id', $managerDepartmentId)
            ->where('daily_activity_slaughter_houses.cost_center_id', $costCenterId)
            ->where('daily_activity_slaughter_houses.ps_group_id', $psGroupId)
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_activity_slaughter_houses.tanggal',
                'users.name as input_by',
                'employees.name as employee_name',
                'product_groups.name as product_group_name',
                'lines.name as line_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_detail_slaughter_houses.total_kg',
                'daily_activity_detail_slaughter_houses.harga_per_kg',
                'daily_activity_detail_slaughter_houses.total_harga',
                'daily_activity_detail_slaughter_houses.lama_packing',
                'daily_activity_detail_slaughter_houses.productivity'
            )
            ->orderByDesc('daily_activity_slaughter_houses.tanggal')
            ->paginate(50)->withQueryString();
 
        return view('pages.manager.daily_activity_slaughter_house.detail', compact(
            'costCenter',
            'psGroup',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }
 
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $detail = DailyActivityDetailSlaughterHouse::with(
                'dailyActivitySlaughterHouse'
            )->findOrFail($id);

            $dailyActivitySlaughterHouse = $detail->dailyActivitySlaughterHouse;

            $employeeId = $dailyActivitySlaughterHouse->employee_id;
            $tanggal = Carbon::parse($dailyActivitySlaughterHouse->tanggal);

            $costCenterId = $dailyActivitySlaughterHouse->cost_center_id;
            $psGroupId = $dailyActivitySlaughterHouse->ps_group_id;
            $lineId = $dailyActivitySlaughterHouse->line_id;
            $dateFrom = $tanggal->format('Y-m-d');

            $detail->delete();

            $remaining = DailyActivityDetailSlaughterHouse::where(
                'daily_activity_slaughter_house_id',
                $dailyActivitySlaughterHouse->id
            )->count();

            if ($remaining === 0) {
                $dailyActivitySlaughterHouse->delete();
            }

            $monthlyDetails = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->where(
                    'daily_activity_slaughter_houses.employee_id',
                    $employeeId
                )
                ->whereMonth(
                    'daily_activity_slaughter_houses.tanggal',
                    $tanggal->month
                )
                ->whereYear(
                    'daily_activity_slaughter_houses.tanggal',
                    $tanggal->year
                )
                ->select(
                    'daily_activity_detail_slaughter_houses.total_kg',
                    'daily_activity_detail_slaughter_houses.total_harga'
                )
                ->get();

            $totalKg = $monthlyDetails->sum('total_kg');
            $totalUpah = $monthlyDetails->sum('total_harga');

            $totalHariKerja = DailyActivitySlaughterHouse::where(
                'employee_id',
                $employeeId
            )
                ->whereMonth('tanggal', $tanggal->month)
                ->whereYear('tanggal', $tanggal->year)
                ->distinct()
                ->count('tanggal');

            $payroll = PenggajianBorongan::where(
                'employee_id',
                $employeeId
            )
                ->where('period_month', $tanggal->month)
                ->where('period_year', $tanggal->year)
                ->first();

            if ($payroll) {

                $jamsostek = round($totalUpah * 0.0489, 2);
                $bpjsKesehatan = round($totalUpah * 0.04, 2);
                $bpjsPensiun = round($totalUpah * 0.02, 2);

                $managemenFeePerDay = 175000 / 25;
                $managemenFee = $totalHariKerja * $managemenFeePerDay;

                $grandTotalUpah =
                    $totalUpah
                    - $jamsostek
                    - $bpjsKesehatan
                    - $bpjsPensiun
                    - $managemenFee;

                $payroll->update([
                    'total_kg' => $totalKg,
                    'total_hari_kerja' => $totalHariKerja,
                    'total_upah' => $totalUpah,
                    'jamsostek' => $jamsostek,
                    'bpjs_kesehatan' => $bpjsKesehatan,
                    'bpjs_pensiun' => $bpjsPensiun,
                    'managemen_fee' => $managemenFee,
                    'grand_total_upah' => $grandTotalUpah,
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'admin-production.daily-activity-slaughter-house.detail',
                    [
                        'costCenter' => $costCenterId,
                        'psGroup' => $psGroupId,
                        'lineId' => $lineId,
                        'date_from' => $dateFrom,
                        'date_to' => $dateFrom,
                    ]
                )
                ->with(
                    'success',
                    'Data berhasil dihapus dan penggajian borongan berhasil diperbarui.'
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
        $detail = DailyActivityDetailSlaughterHouse::with(
            'dailyActivitySlaughterHouse.costCenter',
            'dailyActivitySlaughterHouse.psGroup',
            'dailyActivitySlaughterHouse.productGroup',
            'dailyActivitySlaughterHouse.line',
            'dailyActivitySlaughterHouse.employee'
        )->findOrFail($id);
 
        $productList = Product::where('cost_center_id', $detail->dailyActivitySlaughterHouse->cost_center_id)
            ->orderBy('material_name')
            ->get();
 
        return view('pages.admin_production.daily_activity_slaughter_house.edit', compact(
            'detail',
            'productList'
        ));
    }
 
    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'total_kg' => ['required', 'numeric', 'min:0'],
            'lama_packing' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $detail = DailyActivityDetailSlaughterHouse::with(
                'dailyActivitySlaughterHouse'
            )->findOrFail($id);

            $dailyActivitySlaughterHouse = $detail->dailyActivitySlaughterHouse;

            $employeeId = $dailyActivitySlaughterHouse->employee_id;
            $tanggal = Carbon::parse($dailyActivitySlaughterHouse->tanggal);

            $product = Product::findOrFail($request->product_id);

            $outputKg = (float) $request->total_kg;
            $lamaPacking = (float) $request->lama_packing;
            $hargaPerKg = (float) $product->harga_per_kg;

            $totalHarga = $outputKg * $hargaPerKg;

            $productivity = $lamaPacking > 0
                ? $outputKg / $lamaPacking
                : 0;

            $detail->update([
                'product_id' => $product->id,
                'total_kg' => $outputKg,
                'harga_per_kg' => $hargaPerKg,
                'total_harga' => $totalHarga,
                'lama_packing' => $lamaPacking,
                'productivity' => $productivity,
            ]);

            $monthlyDetails = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->where(
                    'daily_activity_slaughter_houses.employee_id',
                    $employeeId
                )
                ->whereMonth(
                    'daily_activity_slaughter_houses.tanggal',
                    $tanggal->month
                )
                ->whereYear(
                    'daily_activity_slaughter_houses.tanggal',
                    $tanggal->year
                )
                ->select(
                    'daily_activity_detail_slaughter_houses.total_kg',
                    'daily_activity_detail_slaughter_houses.total_harga'
                )
                ->get();

            $totalKg = $monthlyDetails->sum('total_kg');
            $totalUpah = $monthlyDetails->sum('total_harga');

            $totalHariKerja = DailyActivitySlaughterHouse::where(
                'employee_id',
                $employeeId
            )
                ->whereMonth('tanggal', $tanggal->month)
                ->whereYear('tanggal', $tanggal->year)
                ->distinct()
                ->count('tanggal');

            $jamsostek = round($totalUpah * 0.0489, 2);
            $bpjsKesehatan = round($totalUpah * 0.04, 2);
            $bpjsPensiun = round($totalUpah * 0.02, 2);

            $managemenFeePerDay = 175000 / 25;
            $managemenFee = $totalHariKerja * $managemenFeePerDay;

            $grandTotalUpah =
                $totalUpah
                + $jamsostek
                + $bpjsKesehatan
                + $bpjsPensiun
                + $managemenFee;

            PenggajianBorongan::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'period_month' => $tanggal->month,
                    'period_year' => $tanggal->year,
                ],
                [
                    'total_kg' => $totalKg,
                    'total_hari_kerja' => $totalHariKerja,
                    'total_upah' => $totalUpah,
                    'jamsostek' => $jamsostek,
                    'bpjs_kesehatan' => $bpjsKesehatan,
                    'bpjs_pensiun' => $bpjsPensiun,
                    'managemen_fee' => $managemenFee,
                    'grand_total_upah' => $grandTotalUpah,
                ]
            );

            $costCenterId = $dailyActivitySlaughterHouse->cost_center_id;
            $psGroupId = $dailyActivitySlaughterHouse->ps_group_id;
            $lineId = $dailyActivitySlaughterHouse->line_id;
            $dateFrom = $tanggal->format('Y-m-d');

            DB::commit();

            return redirect()
                ->route(
                    'admin-production.daily-activity-slaughter-house.detail',
                    [
                        'costCenter' => $costCenterId,
                        'psGroup' => $psGroupId,
                        'lineId' => $lineId,
                        'date_from' => $dateFrom,
                        'date_to' => $dateFrom,
                    ]
                )
                ->with(
                    'success',
                    'Data berhasil diupdate dan penggajian borongan berhasil diperbarui.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Gagal mengupdate data: ' . $e->getMessage()
                );
        }
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

        $fileName = "daily-activity-slaughter-house-{$fromDate}-to-{$toDate}.xlsx";

        $lineId = $request->line_id;

        return Excel::download(
            new DailyActivitySlaughterHouseExport(
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
     
    public function exportExcelGeneralManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->date_to ?? now()->format('Y-m-d');
        $lineId = $request->line_id;

        $fileName = "daily-activity-slaughter-house-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivitySlaughterHouseExport(
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

        $fileName = "daily-activity-slaughter-house-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivitySlaughterHouseExport(
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
    

    public function exportPdf(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $costCenter = CostCenter::findOrFail($costCenterId);

        $psGroup = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetailSlaughterHouse::with([
            'product',
            'dailyActivitySlaughterHouse.employee',
            'dailyActivitySlaughterHouse.inputBy',
            'dailyActivitySlaughterHouse.line',
        ])
            ->whereHas('dailyActivitySlaughterHouse', function ($q) use (
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate
            ) {
                $q->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->orderBy('daily_activity_slaughter_houses.tanggal')
            ->select('daily_activity_detail_slaughter_houses.*')
            ->get();

        $pdf = Pdf::loadView(
            'pages.admin_production.daily_activity_slaughter_house.pdf',
            [
                'data' => $data,
                'fromDate' => Carbon::parse($fromDate)->format('d M Y'),
                'toDate' => Carbon::parse($toDate)->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-slaughter-house-{$fromDate}-to-{$toDate}.pdf"
        );
    }

    public function exportPdfGeneralManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $costCenter = CostCenter::findOrFail($costCenterId);

        $psGroup = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetailSlaughterHouse::with([
            'product',
            'dailyActivitySlaughterHouse.employee',
            'dailyActivitySlaughterHouse.inputBy',
            'dailyActivitySlaughterHouse.line',
        ])
            ->whereHas('dailyActivitySlaughterHouse', function ($q) use (
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate
            ) {
                $q->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->orderBy('daily_activity_slaughter_houses.tanggal')
            ->select('daily_activity_detail_slaughter_houses.*')
            ->get();

        $pdf = Pdf::loadView(
            'pages.general_manager.daily_activity_slaughter_house.pdf',
            [
                'data' => $data,
                'fromDate' => Carbon::parse($fromDate)->format('d M Y'),
                'toDate' => Carbon::parse($toDate)->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-slaughter-house-{$fromDate}-to-{$toDate}.pdf"
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

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $costCenter = CostCenter::where('department_id', $managerDepartmentId)
            ->findOrFail($costCenterId);

        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)
            ->findOrFail($psGroupId);

        $data = DailyActivityDetailSlaughterHouse::with([
            'product',
            'dailyActivitySlaughterHouse.employee',
            'dailyActivitySlaughterHouse.inputBy',
            'dailyActivitySlaughterHouse.line',
        ])
            ->whereHas('dailyActivitySlaughterHouse', function ($q) use (
                $managerDepartmentId,
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate
            ) {
                $q->where('department_id', $managerDepartmentId)
                    ->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->orderBy('daily_activity_slaughter_houses.tanggal')
            ->select('daily_activity_detail_slaughter_houses.*')
            ->get();

        $pdf = Pdf::loadView(
            'pages.manager.daily_activity_slaughter_house.pdf',
            [
                'data' => $data,
                'fromDate' => Carbon::parse($fromDate)->format('d M Y'),
                'toDate' => Carbon::parse($toDate)->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-activity-slaughter-house-{$fromDate}-to-{$toDate}.pdf"
        );
    }
}
