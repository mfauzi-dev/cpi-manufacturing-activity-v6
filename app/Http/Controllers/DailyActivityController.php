<?php

namespace App\Http\Controllers;

use App\Exports\DailyActivityExport;
use App\Imports\DailyActivityDetailImport;
use App\Imports\DailyActivityImport;
use App\Imports\DailyActivityRevisionImport;
use App\Imports\DailyProductionImport;
use App\Models\CostCenter;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PsGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class DailyActivityController extends Controller
{
    public function create()
    {
        $departmentId = auth()->user()->department_id;

        $department = Department::where('id', $departmentId)->firstOrFail();

        $costCenterList = CostCenter::where('department_id', $departmentId)->orderBy('name')->get();

        return view('pages.admin_production.daily_activity.create', compact(
            'department',
            'costCenterList',
        ));
    }

    public function getEmployees($costCenterId, $psGroupId)
    {
        $employees = Employee::where('cost_center_id', $costCenterId)
            ->where('ps_group_id', $psGroupId)
            ->orderBy('name')
            ->get(['id', 'nik', 'name']);

        return response()->json($employees);
    }

    public function getProducts($costCenterId)
    {
        $products = Product::where('cost_center_id', $costCenterId)
            ->orderBy('material_name')
            ->get([
                'id',
                'material_name',
                'material_code',
                'harga_per_kg'
            ]);

        return response()->json($products);
    }

    /**
     * Cost Center -> PS Group
     */
    public function getPsGroups($costCenterId)
    {
        $groups = PsGroup::where('cost_center_id', $costCenterId)
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);

        return response()->json($groups);
    }

    // public function getEmployees($costCenterId)
    // {
    //     $employees = Employee::where('cost_center_id', $costCenterId)
    //         ->orderBy('name')
    //         ->get([
    //             'id',
    //             'nik',
    //             'name'
    //         ]);

    //     return response()->json($employees);
    // }

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

    public function getCostCentersAllDepartment($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);

        return response()->json($costCenters);
    }

    public function store(Request $request)
    {

        $request->validate([
            'tanggal' => ['required', 'date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'ps_group_id' => ['required', 'exists:ps_groups,id'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.employee_id' => ['required', 'array', 'min:1'],
            'details.*.employee_id.*' => ['exists:employees,id'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.output_kg' => ['required', 'numeric', 'min:0'],
            'details.*.lama_packing' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($request->details as $detail) {

            $product = Product::findOrFail($detail['product_id']);

            $outputKg    = (float) $detail['output_kg'];
            $lamaPacking = (float) $detail['lama_packing'];
            $hargaPerKg  = (float) $product->harga_per_kg;
            $totalHarga  = $outputKg * $hargaPerKg;
             $productivity = $lamaPacking > 0 ? $outputKg / $lamaPacking : 0;

            foreach ($detail['employee_id'] as $employeeId) {

                $dailyActivity = DailyActivity::firstOrCreate([
                    'employee_id' => $employeeId,
                    'tanggal' => $request->tanggal,
                    'cost_center_id' => $request->cost_center_id,
                    'ps_group_id' => $request->ps_group_id,
                    'department_id' => auth()->user()->department_id,
                    'input_by' => auth()->user()->id,
                ]);

                $dailyActivity->details()->create([
                    'product_id'   => $product->id,
                    'total_kg'     => $outputKg,
                    'lama_packing' => $lamaPacking,
                    'harga_per_kg' => $hargaPerKg,
                    'total_harga'  => $totalHarga,
                    'productivity' => $productivity,
                ]);
            }
        }

        return redirect()
            ->route('admin-production.daily-activity.index')
            ->with('success', 'Daily Activity berhasil disimpan.');

    }

    public function index(Request $request)
    {
        $department = auth()->user()->department;

        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');

        $query = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('departments', 'departments.id', '=', 'daily_activities.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activities.cost_center_id')
            ->leftJoin('ps_groups', 'ps_groups.id', '=', 'daily_activities.ps_group_id')
            ->where('cost_centers.department_id', $department->id);

        if ($request->filled('cost_center_id')) {
            $query->where('daily_activities.cost_center_id', $request->cost_center_id);
        }

        if ($request->filled('ps_group_id')) {
            $query->where('daily_activities.ps_group_id', $request->ps_group_id);
        }

        if ($dateFrom) {
            $query->whereDate('daily_activities.tanggal', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('daily_activities.tanggal', '<=', $dateTo);
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activities.tanggal', today());
                break;

            case 'week':
                $query->whereBetween('daily_activities.tanggal', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereMonth('daily_activities.tanggal', now()->month)
                    ->whereYear('daily_activities.tanggal', now()->year);
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
                SUM(daily_activity_details.total_kg) as total_kg,
                SUM(daily_activity_details.total_harga) as total_rupiah
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name'
            )
            ->orderBy('cost_centers.name')
            ->orderBy('ps_groups.id', 'ASC')
            ->paginate(10);

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

        return view('pages.admin_production.daily_activity.index', compact(
            'department',
            'costCenters',
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

        $query = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('departments', 'departments.id', '=', 'daily_activities.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activities.cost_center_id')
            ->join('ps_groups', 'ps_groups.id', '=', 'daily_activities.ps_group_id');

        if ($request->filled('department_id')) {
            $query->where('daily_activities.department_id', $request->department_id);
        }

        if ($request->filled('cost_center_id')) {
            $query->where('daily_activities.cost_center_id', $request->cost_center_id);
        }

        if ($request->filled('ps_group_id')) {
            $query->where('daily_activities.ps_group_id', $request->ps_group_id);
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activities.tanggal', Carbon::today());
                break;

            case 'week':
                $query->whereBetween('daily_activities.tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereMonth('daily_activities.tanggal', Carbon::now()->month)
                    ->whereYear('daily_activities.tanggal', Carbon::now()->year);
                break;
        }

        if ($dateFrom) {
            $query->whereDate('daily_activities.tanggal', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('daily_activities.tanggal', '<=', $dateTo);
        }

        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                SUM(daily_activity_details.total_kg) as total_kg,
                SUM(daily_activity_details.total_harga) as total_rupiah
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name'
            )
            ->orderBy('departments.name')
            ->orderBy('cost_centers.name')
            ->orderBy('ps_groups.id', 'ASC')
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

        return view(
            'pages.general_manager.daily-activity.index',
            compact(
                'summaries',
                'departments',
                'costCenters',
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

        $query = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('departments', 'departments.id', '=', 'daily_activities.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activities.cost_center_id')
            ->join('ps_groups', 'ps_groups.id', '=', 'daily_activities.ps_group_id');

        if ($request->filled('department_id')) {
            $query->where('daily_activities.department_id', $request->department_id);
        }

        if ($request->filled('cost_center_id')) {
            $query->where('daily_activities.cost_center_id', $request->cost_center_id);
        }

        if ($request->filled('ps_group_id')) {
            $query->where('daily_activities.ps_group_id', $request->ps_group_id);
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_activities.tanggal', Carbon::today());
                break;

            case 'week':
                $query->whereBetween('daily_activities.tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereMonth('daily_activities.tanggal', Carbon::now()->month)
                    ->whereYear('daily_activities.tanggal', Carbon::now()->year);
                break;
        }

        if ($dateFrom) {
            $query->whereDate('daily_activities.tanggal', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('daily_activities.tanggal', '<=', $dateTo);
        }
        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,
                SUM(daily_activity_details.total_kg) as total_kg,
                SUM(daily_activity_details.total_harga) as total_rupiah
            ")
            ->groupBy(
                'departments.id',
                'departments.name',
                'cost_centers.id',
                'cost_centers.name',
                'ps_groups.id',
                'ps_groups.name'
            )
            ->orderBy('departments.name')
            ->orderBy('cost_centers.name')
            ->orderBy('ps_groups.id', 'ASC')
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

        return view(
            'pages.manager.daily-activity.index',
            compact(
                'summaries',
                'departments',
                'costCenters',
                'grandTotalKg',
                'grandTotalRupiah',
                'grandHargaPerKg',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function detail(Request $request, $costCenterId, $psGroupId)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup  = PsGroup::findOrFail($psGroupId);

        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));


        $details = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('products', 'products.id', '=', 'daily_activity_details.product_id')
            ->join('users', 'users.id', '=', 'daily_activities.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activities.employee_id')
            ->where('daily_activities.cost_center_id', $costCenterId)
            ->where('daily_activities.ps_group_id', $psGroupId)
            ->whereBetween('daily_activities.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_activity_details.id',
                'daily_activities.tanggal',
                'users.name as user_name',
                'employees.name as employee_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_details.total_kg',
                'daily_activity_details.lama_packing',
                'daily_activity_details.harga_per_kg',
                'daily_activity_details.total_harga',
                'daily_activity_details.productivity'
            )
            ->orderByDesc('daily_activities.tanggal')
            ->orderByDesc('daily_activity_details.product_id')
            ->orderByDesc('daily_activity_details.id')
            ->paginate(100)->withQueryString();

        return view('pages.admin_production.daily_activity.detail', compact(
            'costCenter',
            'psGroup',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }

    public function generalManagerDetail(Request $request, $costCenterId, $psGroupId)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup  = PsGroup::findOrFail($psGroupId);

        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));

        $psGroups = PsGroup::where('cost_center_id', $costCenterId)
            ->orderBy('name')
            ->get();

        $details = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('products', 'products.id', '=', 'daily_activity_details.product_id')
            ->join('users', 'users.id', '=', 'daily_activities.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activities.employee_id')
            ->where('daily_activities.cost_center_id', $costCenterId)
            ->where('daily_activities.ps_group_id', $psGroupId)
            ->whereBetween('daily_activities.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_activities.tanggal',
                'users.name as input_by',
                'employees.name as employee_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_details.total_kg',
                'daily_activity_details.lama_packing',
                'daily_activity_details.harga_per_kg',
                'daily_activity_details.total_harga',
                'daily_activity_details.productivity'
            )
            ->orderByDesc('daily_activities.tanggal')
            ->paginate(100)->withQueryString();

        return view('pages.general_manager.daily-activity.detail', compact(
            'costCenter',
            'psGroup',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }

    public function managerDetail(Request $request, $costCenterId, $psGroupId)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup  = PsGroup::findOrFail($psGroupId);

        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));

        $psGroups = PsGroup::where('cost_center_id', $costCenterId)
            ->orderBy('name')
            ->get();

        $details = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('products', 'products.id', '=', 'daily_activity_details.product_id')
            ->join('users', 'users.id', '=', 'daily_activities.input_by')
            ->join('employees', 'employees.id', '=', 'daily_activities.employee_id')
            ->where('daily_activities.cost_center_id', $costCenterId)
            ->where('daily_activities.ps_group_id', $psGroupId)
            ->whereBetween('daily_activities.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_activities.tanggal',
                'users.name as input_by',
                'employees.name as employee_name',
                'products.material_code',
                'products.material_name',
                'daily_activity_details.total_kg',
                'daily_activity_details.lama_packing',
                'daily_activity_details.harga_per_kg',
                'daily_activity_details.total_harga',
                'daily_activity_details.productivity'
            )
            ->orderByDesc('daily_activities.tanggal')
            ->paginate(100)->withQueryString();

        return view('pages.manager.daily-activity.detail', compact(
            'costCenter',
            'psGroup',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }

    // public function importPage()
    // {
    //     $departmentId = auth()->user()->department_id;

    //     $department = Department::where('id', $departmentId)->firstOrFail();

    //     $costCenterList = CostCenter::where('department_id', $departmentId)->orderBy('name')->get();

    //     return view('pages.admin_production.daily_activity.import', compact(
    //         'department',
    //         'costCenterList',
    //     ));
    // }

    // public function upload(Request $request)
    // {
    //     $request->validate([
    //         'tanggal'        => 'required|date',
    //         'cost_center_id' => 'required|exists:cost_centers,id',
    //         'ps_group_id'    => 'required|exists:ps_groups,id',
    //         'file'           => 'required|mimes:xlsx,xls',
    //     ]);

    //     try {
    //         $costCenter = CostCenter::findOrFail($request->cost_center_id);
    //         $psGroup    = PsGroup::findOrFail($request->ps_group_id);

    //         $import = new DailyActivityDetailImport(
    //             $request->tanggal,
    //             $costCenter,
    //             $psGroup,
    //             auth()->user()->department_id,
    //             auth()->id()
    //         );

    //         Excel::import($import, $request->file('file'));

    //         $summary = "Karyawan baru: {$import->headersCreated}, update: {$import->headersUpdated}. "
    //                 . "Detail baru: {$import->detailsCreated}, update: {$import->detailsUpdated}.";

    //         if (!empty($import->errors)) {
    //             return back()
    //                 ->withInput()
    //                 ->with('warning', "Import selesai dengan catatan. {$summary}")
    //                 ->with('import_errors', $import->errors);
    //         }

    //         return redirect()
    //             ->route('admin-production.daily-activity.index')
    //             ->with('success', "Daily activity berhasil diimport. {$summary}");

    //     } catch (\Throwable $e) {
    //         return back()->withInput()->with('error', $e->getMessage());
    //     }
    // }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $detail = DailyActivityDetail::findOrFail($id);
            $dailyActivityId = $detail->daily_activity_id;

            $detail->delete();

            // kalau daily_activity sudah tidak punya detail lagi, hapus juga headernya
            $remaining = DailyActivityDetail::where('daily_activity_id', $dailyActivityId)->count();

            if ($remaining === 0) {
                DailyActivity::destroy($dailyActivityId);
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
        $detail = DailyActivityDetail::with('dailyActivity.costCenter', 'dailyActivity.psGroup', 'dailyActivity.employee')
            ->findOrFail($id);

        $productList = Product::where('cost_center_id', $detail->dailyActivity->cost_center_id)
            ->orderBy('material_name')
            ->get();

        return view('pages.admin_production.daily_activity.edit', compact(
            'detail',
            'productList'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id'    => ['required', 'exists:products,id'],
            'output_kg'     => ['required', 'numeric', 'min:0'],
            'lama_packing'  => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $detail = DailyActivityDetail::with('product', 'dailyActivity')->findOrFail($id);

            $product = Product::findOrFail($request->product_id);

            $outputKg     = (float) $request->output_kg;
            $lamaPacking  = (float) $request->lama_packing;
            $hargaPerKg   = (float) $product->harga_per_kg;
            $totalHarga   = $outputKg * $hargaPerKg;
            $productivity = $lamaPacking > 0 ? $outputKg / $lamaPacking : 0;

            $detail->update([
                'product_id'   => $product->id,
                'total_kg'     => $outputKg,
                'lama_packing' => $lamaPacking,
                'harga_per_kg' => $hargaPerKg,
                'total_harga'  => $totalHarga,
                'productivity' => $productivity,
            ]);

            $costCenterId = $detail->dailyActivity->cost_center_id;
            $psGroupId    = $detail->dailyActivity->ps_group_id;
            $dateForm     = $detail->dailyActivity->tanggal->format('Y-m-d');

            DB::commit();

            return redirect()
                ->route('admin-production.daily-activity.detail', [
                    'costCenter' => $costCenterId,
                    'psGroup'    => $psGroupId,
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

    public function exportExcel(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');

        $fileName = "daily-activity-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyActivityExport($costCenterId, $psGroupId, $fromDate, $toDate),
            $fileName
        );
    }

    public function exportPdf(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetail::with(['product', 'dailyActivity.employee', 'dailyActivity.inputBy'])
            ->whereHas('dailyActivity', function ($q) use ($costCenterId, $psGroupId, $fromDate, $toDate) {
                $q->where('cost_center_id', $costCenterId)
                ->where('ps_group_id', $psGroupId)
                ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->orderBy('daily_activities.tanggal')
            ->select('daily_activity_details.*')
            ->get();

        $pdf = Pdf::loadView('pages.admin_production.daily_activity.pdf', [
            'data'           => $data,
            'fromDate'       => \Carbon\Carbon::parse($fromDate)->format('d M Y'),
            'toDate'         => \Carbon\Carbon::parse($toDate)->format('d M Y'),
            'costCenterName' => $costCenter->name,
            'psGroupName'    => $psGroup->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("daily-activity-{$fromDate}-to-{$toDate}.pdf");
    }

    public function exportPdfGeneralManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetail::with(['product', 'dailyActivity.employee', 'dailyActivity.inputBy'])
            ->whereHas('dailyActivity', function ($q) use ($costCenterId, $psGroupId, $fromDate, $toDate) {
                $q->where('cost_center_id', $costCenterId)
                ->where('ps_group_id', $psGroupId)
                ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->orderBy('daily_activities.tanggal')
            ->select('daily_activity_details.*')
            ->get();

        $pdf = Pdf::loadView('pages.general_manager.daily-activity.pdf', [
            'data'           => $data,
            'fromDate'       => \Carbon\Carbon::parse($fromDate)->format('d M Y'),
            'toDate'         => \Carbon\Carbon::parse($toDate)->format('d M Y'),
            'costCenterName' => $costCenter->name,
            'psGroupName'    => $psGroup->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("daily-activity-{$fromDate}-to-{$toDate}.pdf");
    }

    public function exportPdfManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyActivityDetail::with(['product', 'dailyActivity.employee', 'dailyActivity.inputBy'])
            ->whereHas('dailyActivity', function ($q) use ($costCenterId, $psGroupId, $fromDate, $toDate) {
                $q->where('cost_center_id', $costCenterId)
                ->where('ps_group_id', $psGroupId)
                ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->orderBy('daily_activities.tanggal')
            ->select('daily_activity_details.*')
            ->get();

        $pdf = Pdf::loadView('pages.manager.daily-activity.pdf', [
            'data'           => $data,
            'fromDate'       => \Carbon\Carbon::parse($fromDate)->format('d M Y'),
            'toDate'         => \Carbon\Carbon::parse($toDate)->format('d M Y'),
            'costCenterName' => $costCenter->name,
            'psGroupName'    => $psGroup->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("daily-activity-{$fromDate}-to-{$toDate}.pdf");
    }
}
