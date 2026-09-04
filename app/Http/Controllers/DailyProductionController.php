<?php

namespace App\Http\Controllers;

use App\Exports\DailyProductionExport;
use App\Models\CostCenter;
use App\Models\DailyProduction;
use App\Models\DailyProductionDetail;
use App\Models\Department;
use App\Models\Product;
use App\Models\PsGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DailyProductionController extends Controller
{
    
    public function create()
    {
        $departmentId = auth()->user()->department_id;

        $department = Department::findOrFail($departmentId);

        $costCenterList = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $costCenterIds = $costCenterList->pluck('id');

        $productList = Product::whereIn('cost_center_id', $costCenterIds)
            ->orderBy('material_name')
            ->get([
                'id',
                'material_name',
                'material_code',
                'cost_center_id',
                'harga_per_kg'
            ]);

        return view('pages.admin_production.daily_production.create', compact(
            'department',
            'costCenterList',
            'productList'
        ));
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

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'ps_group_id' => ['required', 'exists:ps_groups,id'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.total_kg' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $dailyProduction = DailyProduction::firstOrCreate([
                'tanggal' => $request->tanggal,
                'cost_center_id' => $request->cost_center_id,
                'ps_group_id' => $request->ps_group_id,
            ], [
                'department_id' => auth()->user()->department_id,
                'input_by' => auth()->id(),
            ]);

            foreach ($request->details as $detail) {

                $product = Product::findOrFail($detail['product_id']);

                $totalKg    = (float) $detail['total_kg'];
                $hargaPerKg = (float) $product->harga_per_kg;
                $totalHarga = $totalKg * $hargaPerKg;

                $dailyProduction->details()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'total_kg'     => $totalKg,
                        'harga_per_kg' => $hargaPerKg,
                        'total_harga'  => $totalHarga,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('admin-production.daily-production.index')
                ->with('success', 'Daily Production berhasil disimpan.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $department = auth()->user()->department;

        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');

        $query = DailyProductionDetail::query()
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->join('departments', 'departments.id', '=', 'daily_productions.department_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_productions.cost_center_id')
            ->leftJoin('ps_groups', 'ps_groups.id', '=', 'daily_productions.ps_group_id')
            ->where('cost_centers.department_id', $department->id);

        if ($request->filled('cost_center_id')) {
            $query->where('daily_productions.cost_center_id', $request->cost_center_id);
        }

        if ($request->filled('ps_group_id')) {
            $query->where('daily_productions.ps_group_id', $request->ps_group_id);
        }

        if ($dateFrom) {
            $query->whereDate('daily_productions.tanggal', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('daily_productions.tanggal', '<=', $dateTo);
        }

        switch ($request->quick_filter) {
            case 'today':
                $query->whereDate('daily_productions.tanggal', today());
                break;

            case 'week':
                $query->whereBetween('daily_productions.tanggal', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'month':
                $query->whereMonth('daily_productions.tanggal', now()->month)
                    ->whereYear('daily_productions.tanggal', now()->year);
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
                SUM(daily_production_details.total_kg) as total_kg,
                SUM(daily_production_details.total_harga) as total_rupiah
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

        return view('pages.admin_production.daily_production.index', compact(
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

    public function detail(Request $request, $costCenterId, $psGroupId)
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup  = PsGroup::findOrFail($psGroupId);

        $dateFrom  = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));

        $details = DailyProductionDetail::query()
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->join('products', 'products.id', '=', 'daily_production_details.product_id')
            ->join('users', 'users.id', '=', 'daily_productions.input_by')
            ->where('daily_productions.cost_center_id', $costCenterId)
            ->where('daily_productions.ps_group_id', $psGroupId)
            ->whereBetween('daily_productions.tanggal', [$dateFrom, $dateTo])
            ->select(
                'daily_production_details.id',
                'daily_productions.tanggal',
                'users.name as user_name',
                'products.material_code',
                'products.material_name',
                'daily_production_details.total_kg',
                'daily_production_details.harga_per_kg',
                'daily_production_details.total_harga'
            )
            ->orderBy('daily_productions.tanggal')
            ->orderByDesc('daily_productions.created_at')
            ->orderByDesc('daily_production_details.product_id')
            ->paginate(100)->withQueryString();

        return view('pages.admin_production.daily_production.detail', compact(
            'costCenter',
            'psGroup',
            'details',
            'dateFrom',
            'dateTo',
        ));
    }

    public function edit($id)
    {
        $detail = DailyProductionDetail::with('dailyProduction.costCenter', 'dailyProduction.psGroup')
            ->findOrFail($id);

        $productList = Product::where('cost_center_id', $detail->dailyProduction->cost_center_id)
            ->orderBy('material_name')
            ->get();

        return view('pages.admin_production.daily_production.edit', compact(
            'detail',
            'productList'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'total_kg'   => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $detail = DailyProductionDetail::with('product', 'dailyProduction')->findOrFail($id);

            $product = Product::findOrFail($request->product_id);

            $totalKg    = (float) $request->total_kg;
            $hargaPerKg = (float) $product->harga_per_kg;
            $totalHarga = $totalKg * $hargaPerKg;

            $detail->update([
                'product_id'   => $product->id,
                'total_kg'     => $totalKg,
                'harga_per_kg' => $hargaPerKg,
                'total_harga'  => $totalHarga,
            ]);

            $costCenterId = $detail->dailyProduction->cost_center_id;
            $psGroupId    = $detail->dailyProduction->ps_group_id;
            $dateForm     = $detail->dailyProduction->tanggal->format('Y-m-d');

            DB::commit();

            return redirect()
                ->route('admin-production.daily-production.detail', [
                    'costCenter' => $costCenterId,
                    'psGroup'    => $psGroupId,
                    'date_from'  => $dateForm,
                    'date_to'    => $dateForm,
                ])
                ->with('success', 'Data berhasil diupdate.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $detail = DailyProductionDetail::findOrFail($id);
            $dailyProductionId = $detail->daily_production_id;

            $detail->delete();

            // kalau daily_production sudah tidak punya detail lagi, hapus juga headernya
            $remaining = DailyProductionDetail::where('daily_production_id', $dailyProductionId)->count();

            if ($remaining === 0) {
                DailyProduction::destroy($dailyProductionId);
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

    public function managerIndex(Request $request)
    {
        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');

        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');


        $query = DailyProductionDetail::query()
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->join(
                'departments',
                'departments.id',
                '=',
                'daily_productions.department_id'
            )
            ->join(
                'cost_centers',
                'cost_centers.id',
                '=',
                'daily_productions.cost_center_id'
            )
            ->join(
                'ps_groups',
                'ps_groups.id',
                '=',
                'daily_productions.ps_group_id'
            )
            ->where('daily_productions.department_id', $managerDepartmentId);

        if ($request->filled('department_id')) {
            $query->where(
                'daily_productions.department_id',
                $request->department_id
            );
        }

        if ($request->filled('cost_center_id')) {
            $query->where(
                'daily_productions.cost_center_id',
                $request->cost_center_id
            );
        }

        if ($request->filled('ps_group_id')) {
            $query->where(
                'daily_productions.ps_group_id',
                $request->ps_group_id
            );
        }

        switch ($request->quick_filter) {

            case 'today':
                $query->whereDate(
                    'daily_productions.tanggal',
                    Carbon::today()
                );
                break;

            case 'week':
                $query->whereBetween(
                    'daily_productions.tanggal',
                    [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]
                );
                break;

            case 'month':
                $query->whereMonth(
                    'daily_productions.tanggal',
                    Carbon::now()->month
                )
                ->whereYear(
                    'daily_productions.tanggal',
                    Carbon::now()->year
                );
                break;
        }

        if ($dateFrom) {
            $query->whereDate(
                'daily_productions.tanggal',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'daily_productions.tanggal',
                '<=',
                $dateTo
            );
        }

        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,

                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,

                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,

                SUM(daily_production_details.total_kg) as total_kg,
                SUM(daily_production_details.total_harga) as total_rupiah
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
            ->orderBy('ps_groups.name')
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
        $managerDepartment = Department::find($managerDepartmentId);

        return view(
            'pages.manager.daily-production.index',
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


    public function managerDetail(Request $request, $costCenterId, $psGroupId) 
    {

        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $costCenter = CostCenter::where('department_id', $managerDepartmentId)->findOrFail($costCenterId);
        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)->findOrFail($psGroupId);

        $dateFrom = $request->input(
            'date_from',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dateTo = $request->input(
            'date_to',
            now()->format('Y-m-d')
        );

        $details = DailyProductionDetail::query()
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'daily_production_details.product_id'
            )
            ->join(
                'users',
                'users.id',
                '=',
                'daily_productions.input_by'
            )
            ->where('daily_productions.department_id', $managerDepartmentId)
            ->where(
                'daily_productions.cost_center_id',
                $costCenterId
            )
            ->where(
                'daily_productions.ps_group_id',
                $psGroupId
            )
            ->whereBetween(
                'daily_productions.tanggal',
                [$dateFrom, $dateTo]
            )
            ->select(
                'daily_productions.tanggal',

                'users.name as input_by',

                'products.material_code',
                'products.material_name',

                'daily_production_details.total_kg',
                'daily_production_details.harga_per_kg',
                'daily_production_details.total_harga'
            )
            ->orderBy('daily_productions.tanggal')
            ->orderByDesc('daily_productions.created_at')
            ->orderByDesc('daily_production_details.product_id')
            ->paginate(100)
            ->withQueryString();

        return view(
            'pages.manager.daily-production.detail',
            compact(
                'costCenter',
                'psGroup',
                'details',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function generalManagerIndex(Request $request)
    {
        $dateFrom = $request->input('start_date');
        $dateTo   = $request->input('end_date');

        $query = DailyProductionDetail::query()
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->join(
                'departments',
                'departments.id',
                '=',
                'daily_productions.department_id'
            )
            ->join(
                'cost_centers',
                'cost_centers.id',
                '=',
                'daily_productions.cost_center_id'
            )
            ->join(
                'ps_groups',
                'ps_groups.id',
                '=',
                'daily_productions.ps_group_id'
            );

        if ($request->filled('department_id')) {
            $query->where(
                'daily_productions.department_id',
                $request->department_id
            );
        }

        if ($request->filled('cost_center_id')) {
            $query->where(
                'daily_productions.cost_center_id',
                $request->cost_center_id
            );
        }

        if ($request->filled('ps_group_id')) {
            $query->where(
                'daily_productions.ps_group_id',
                $request->ps_group_id
            );
        }

        switch ($request->quick_filter) {

            case 'today':
                $query->whereDate(
                    'daily_productions.tanggal',
                    Carbon::today()
                );
                break;

            case 'week':
                $query->whereBetween(
                    'daily_productions.tanggal',
                    [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]
                );
                break;

            case 'month':
                $query->whereMonth(
                    'daily_productions.tanggal',
                    Carbon::now()->month
                )
                ->whereYear(
                    'daily_productions.tanggal',
                    Carbon::now()->year
                );
                break;
        }

        if ($dateFrom) {
            $query->whereDate(
                'daily_productions.tanggal',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'daily_productions.tanggal',
                '<=',
                $dateTo
            );
        }

        $summaries = $query
            ->selectRaw("
                departments.id as department_id,
                departments.name as department_name,

                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,

                ps_groups.id as ps_group_id,
                ps_groups.name as ps_group_name,

                SUM(daily_production_details.total_kg) as total_kg,
                SUM(daily_production_details.total_harga) as total_rupiah
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
            ->orderBy('ps_groups.name')
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
            'pages.general_manager.daily-production.index',
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


    public function generalManagerDetail(Request $request,$costCenterId,$psGroupId) 
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $dateFrom = $request->input(
            'date_from',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dateTo = $request->input(
            'date_to',
            now()->format('Y-m-d')
        );

        $details = DailyProductionDetail::query()
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'daily_production_details.product_id'
            )
            ->join(
                'users',
                'users.id',
                '=',
                'daily_productions.input_by'
            )
            ->where(
                'daily_productions.cost_center_id',
                $costCenterId
            )
            ->where(
                'daily_productions.ps_group_id',
                $psGroupId
            )
            ->whereBetween(
                'daily_productions.tanggal',
                [$dateFrom, $dateTo]
            )
            ->select(
                'daily_productions.tanggal',

                'users.name as input_by',

                'products.material_code',
                'products.material_name',

                'daily_production_details.total_kg',
                'daily_production_details.harga_per_kg',
                'daily_production_details.total_harga'
            )
            ->orderBy('daily_productions.tanggal')
            ->orderByDesc('daily_productions.created_at')
            ->orderByDesc('daily_production_details.product_id')
            ->paginate(100)
            ->withQueryString();

        return view(
            'pages.general_manager.daily-production.detail',
            compact(
                'costCenter',
                'psGroup',
                'details',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function exportPdfManager(Request $request, $costCenterId, $psGroupId) 
    {
        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $costCenter = CostCenter::where('department_id', $managerDepartmentId)->findOrFail($costCenterId);
        $psGroup = PsGroup::where('cost_center_id', $costCenter->id)->findOrFail($psGroupId);

        $data = DailyProductionDetail::with([
                'product',
                'dailyProduction.inputBy'
            ])
            ->whereHas('dailyProduction', function ($q) use (
                $managerDepartmentId,
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate ) 
                {
                $q->where('department_id', $managerDepartmentId)
                    ->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween(
                        'tanggal',
                        [$fromDate, $toDate]
                    );
            })
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->orderBy('daily_productions.tanggal')
            ->select('daily_production_details.*')
            ->get();

        $pdf = Pdf::loadView(
            'pages.manager.daily-production.pdf',
            [
                'data' => $data,
                'fromDate' => Carbon::parse($fromDate)
                    ->format('d M Y'),
                'toDate' => Carbon::parse($toDate)
                    ->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-production-{$fromDate}-to-{$toDate}.pdf"
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

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyProductionDetail::with([
                'product',
                'dailyProduction.inputBy'
            ])
            ->whereHas('dailyProduction', function ($q) use (
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate
            ) {
                $q->where('cost_center_id', $costCenterId)
                    ->where('ps_group_id', $psGroupId)
                    ->whereBetween(
                        'tanggal',
                        [$fromDate, $toDate]
                    );
            })
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->orderBy('daily_productions.tanggal')
            ->select('daily_production_details.*')
            ->get();

        $pdf = Pdf::loadView(
            'pages.general_manager.daily-production.pdf',
            [
                'data' => $data,
                'fromDate' => Carbon::parse($fromDate)
                    ->format('d M Y'),
                'toDate' => Carbon::parse($toDate)
                    ->format('d M Y'),
                'costCenterName' => $costCenter->name,
                'psGroupName' => $psGroup->name,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            "daily-production-{$fromDate}-to-{$toDate}.pdf"
        );
    }

    public function exportExcelGeneralManager(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');

        $fileName = "daily-production-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyProductionExport($costCenterId, $psGroupId, $fromDate, $toDate),
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

        $costCenter = CostCenter::where(
            'department_id',
            $managerDepartmentId
        )->findOrFail($costCenterId);

        $psGroup = PsGroup::where(
            'cost_center_id',
            $costCenter->id
        )->findOrFail($psGroupId);

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $fileName = "daily-production-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyProductionExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $managerDepartmentId
            ),
            $fileName
        );
    }

    public function exportExcel(Request $request,$costCenterId,$psGroupId) 
    {
        $adminDepartmentId = auth()->user()->department_id;

        abort_unless(
            $adminDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $costCenter = CostCenter::where(
            'department_id',
            $adminDepartmentId
        )->findOrFail($costCenterId);

        $psGroup = PsGroup::where(
            'cost_center_id',
            $costCenter->id
        )->findOrFail($psGroupId);

        $fromDate = $request->date_from
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->date_to
            ?? now()->format('Y-m-d');

        $fileName = "daily-production-{$fromDate}-to-{$toDate}.xlsx";

        return Excel::download(
            new DailyProductionExport(
                $costCenterId,
                $psGroupId,
                $fromDate,
                $toDate,
                $adminDepartmentId
            ),
            $fileName
        );
    }

    public function exportPdf(Request $request, $costCenterId, $psGroupId)
    {
        $fromDate = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->date_to ?? now()->format('Y-m-d');

        $costCenter = CostCenter::findOrFail($costCenterId);
        $psGroup    = PsGroup::findOrFail($psGroupId);

        $data = DailyProductionDetail::with(['product', 'dailyProduction.inputBy'])
            ->whereHas('dailyProduction', function ($q) use ($costCenterId, $psGroupId, $fromDate, $toDate) {
                $q->where('cost_center_id', $costCenterId)
                ->where('ps_group_id', $psGroupId)
                ->whereBetween('tanggal', [$fromDate, $toDate]);
            })
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->orderBy('daily_productions.tanggal')
            ->select('daily_production_details.*')
            ->get();

        $pdf = Pdf::loadView('pages.admin_production.daily_production.pdf', [
            'data'           => $data,
            'fromDate'       => Carbon::parse($fromDate)->format('d M Y'),
            'toDate'         => Carbon::parse($toDate)->format('d M Y'),
            'costCenterName' => $costCenter->name,
            'psGroupName'    => $psGroup->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("daily-production-{$fromDate}-to-{$toDate}.pdf");
    }
}
