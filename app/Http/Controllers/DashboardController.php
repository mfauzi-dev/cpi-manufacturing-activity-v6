<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\CostCenter;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\DailyActivityDetailFurther;
use App\Models\DailyActivityDetailSlaughterHouse;
use App\Models\DailyActivityFurther;
use App\Models\DailyActivitySlaughterHouse;
use App\Models\DailyProductionDetail;
use App\Models\Deduction;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDailyEarning;
use App\Models\Employees;
use App\Models\Foundation;
use App\Models\Overtime;
use App\Models\PayrollBulanan;
use App\Models\PayrollHarian;
use App\Models\Position;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

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

    public function index(Request $request)
    {
        $role = auth()->user()->role->name;

        switch ($role) {
            case 'Admin':
                return $this->adminDashboard($request);

            case 'General Manager':
                return $this->generalManagerDashboard($request);

            case 'Manager':
                return $this->managerDashboard($request);

            case 'Admin Production':
                return $this->adminProductionDashboard();

            default:
                abort(403);
        }
    }

    private function adminDashboard(Request $request)
    {
        $today = Carbon::today();

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)
            : $today;

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)
            : $today;

        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        $attendanceQuery = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate]);

        $employeeQuery = Employee::query();

        if ($departmentId) {
            $attendanceQuery->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });

            $employeeQuery->where('department_id', $departmentId);
        }

        if ($costCenterId) {
            $attendanceQuery->whereHas('employee', function ($q) use ($costCenterId) {
                $q->where('cost_center_id', $costCenterId);
            });

            $employeeQuery->where('cost_center_id', $costCenterId);
        }

        $totalEmployee = (clone $employeeQuery)->count();

        $hadir = (clone $attendanceQuery)
            ->where('status', 'Hadir')
            ->count();

        $izin = (clone $attendanceQuery)
            ->where('status', 'Izin')
            ->count();

        $sakit = (clone $attendanceQuery)
            ->where('status', 'Sakit')
            ->count();

        $alpha = (clone $attendanceQuery)
            ->where('status', 'Alpha')
            ->count();

        $attendanceProgress = $totalEmployee > 0
            ? (($hadir + $izin + $sakit + $alpha) / $totalEmployee) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Daily Activity
        |--------------------------------------------------------------------------
        */

        $dailyActivity = DailyActivityDetail::query()
            ->join(
                'daily_activities',
                'daily_activities.id',
                '=',
                'daily_activity_details.daily_activity_id'
            );

        if ($departmentId) {
            $dailyActivity->where('daily_activities.department_id', $departmentId);
        }

        if ($costCenterId) {
            $dailyActivity->where('daily_activities.cost_center_id', $costCenterId);
        }

        $dailyActivity->whereBetween('daily_activities.tanggal', [
            $startDate,
            $endDate
        ]);

        $summary = (clone $dailyActivity)
            ->selectRaw("
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah,
                COUNT(DISTINCT daily_activity_id) as total_activity
            ")
            ->first();

        // $totalPac = $summary->total_pac ?? 0;
        $totalKg = $summary->total_kg ?? 0;
        $totalRupiah = $summary->total_rupiah ?? 0;
        $totalActivity = $summary->total_activity ?? 0;

        $averageHargaKg = $totalKg > 0
            ? $totalRupiah / $totalKg
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Progress Daily Activity
        |--------------------------------------------------------------------------
        */

        $costCenterQuery = CostCenter::query();

        if ($departmentId) {
            $costCenterQuery->where('department_id', $departmentId);
        }

        $totalCostCenter = (clone $costCenterQuery)->count();

        $inputCostCenter = DailyActivity::query()
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($costCenterId, fn($q) => $q->where('cost_center_id', $costCenterId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->distinct('cost_center_id')
            ->count('cost_center_id');

        $dailyActivityProgress = $totalCostCenter > 0
            ? ($inputCostCenter / $totalCostCenter) * 100
            : 0;

        $departmentSummary = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('departments', 'departments.id', '=', 'daily_activities.department_id')
            ->selectRaw("
                departments.id,
                departments.name,

                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah
            ")
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate])
            ->groupBy(
                'departments.id',
                'departments.name'
            )
            ->orderBy('departments.name')
            ->get();

        foreach ($departmentSummary as $department) {

            $department->harga_per_kg =
                $department->total_kg > 0
                    ? $department->total_rupiah / $department->total_kg
                    : 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Belum Input Daily Activity
        |--------------------------------------------------------------------------
        */

        $costCenterInput = DailyActivity::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('cost_center_id');

        $notInputDailyActivity = CostCenter::query()
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->whereNotIn('id', $costCenterInput)
            ->with('department')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Belum Absen
        |--------------------------------------------------------------------------
        */

        $employeeAttendance = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('employee_id');

        $notAttendance = Employee::query()
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($costCenterId, fn($q) => $q->where('cost_center_id', $costCenterId))
            ->whereNotIn('id', $employeeAttendance)
            ->get();

        $recentActivities = DailyActivity::with([
            'costCenter',
        ])
            ->latest()
            ->take(10)
            ->get();

        $departments = Department::orderBy('name')->get();


        return view('pages.dashboard.admin', compact(
            'startDate',
            'endDate',

            'totalEmployee',
            'hadir',
            'izin',
            'sakit',
            'alpha',

            'attendanceProgress',

            'totalActivity',
            'totalKg',
            'totalRupiah',
            'averageHargaKg',

            'dailyActivityProgress',

            'departmentSummary',
            'departments',

            'notAttendance',
            'notInputDailyActivity',

            'recentActivities'
        ));
    }

    private function generalManagerDashboard(Request $request)
    {
        $today = Carbon::today();

        if ($request->start_date) {
            $startDate = Carbon::parse($request->start_date);
        } else {
            $startDate = $today->copy()->startOfMonth();
        }

        if ($request->end_date) {
            $endDate = Carbon::parse($request->end_date);
        } else {
            $endDate = $today;
        }

        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

        $attendanceQuery = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate]);

        $employeeQuery = Employee::query();

        if ($departmentId) {
            $attendanceQuery->whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });

            $employeeQuery->where('department_id', $departmentId);
        }

        if ($costCenterId) {
            $attendanceQuery->whereHas('employee', function ($query) use ($costCenterId) {
                $query->where('cost_center_id', $costCenterId);
            });

            $employeeQuery->where('cost_center_id', $costCenterId);
        }

        $totalEmployee = $employeeQuery->count();

        $hadir = (clone $attendanceQuery)
            ->where('status', 'Hadir')
            ->count();

        $izin = (clone $attendanceQuery)
            ->where('status', 'Izin')
            ->count();

        $sakit = (clone $attendanceQuery)
            ->where('status', 'Sakit')
            ->count();

        $alpha = (clone $attendanceQuery)
            ->where('status', 'Alpha')
            ->count();

        $totalAttendance = $hadir + $izin + $sakit + $alpha;

        if ($totalEmployee > 0) {
            $attendanceProgress = ($totalAttendance / $totalEmployee) * 100;
        } else {
            $attendanceProgress = 0;
        }

        $summaryBorongan = DailyActivityDetail::query()
            ->join(
                'daily_activities',
                'daily_activities.id',
                '=',
                'daily_activity_details.daily_activity_id'
            )
            ->whereBetween(
                'daily_activities.tanggal',
                [$startDate, $endDate]
            )
            ->selectRaw('
                SUM(daily_activity_details.total_kg) as total_kg,
                SUM(daily_activity_details.total_harga) as total_rupiah,
                COUNT(DISTINCT daily_activity_id) as total_activity
            ')
            ->first();

        $totalKgBorongan = $summaryBorongan->total_kg ?? 0;
        $totalRupiahBorongan = $summaryBorongan->total_rupiah ?? 0;
        $totalActivityBorongan = $summaryBorongan->total_activity ?? 0;

        $summaryHarian = DailyProductionDetail::query()
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->whereBetween(
                'daily_productions.tanggal',
                [$startDate, $endDate]
            )
            ->selectRaw('
                SUM(daily_production_details.total_kg) as total_kg,
                SUM(daily_production_details.total_harga) as total_rupiah,
                COUNT(DISTINCT daily_production_id) as total_activity
            ')
            ->first();

        $totalKgHarian = $summaryHarian->total_kg ?? 0;
        $totalRupiahHarian = $summaryHarian->total_rupiah ?? 0;
        $totalActivityHarian = $summaryHarian->total_activity ?? 0;

        $summaryFurther = DailyActivityDetailFurther::query()
            ->join(
                'daily_activity_furthers',
                'daily_activity_furthers.id',
                '=',
                'daily_activity_detail_furthers.daily_activity_further_id'
            )
            ->whereBetween(
                'daily_activity_furthers.tanggal',
                [$startDate, $endDate]
            )
            ->selectRaw('
                SUM(daily_activity_detail_furthers.total_kg) as total_kg
            ')
            ->first();

        $totalKgFurther = $summaryFurther->total_kg ?? 0;

        $summarySlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->whereBetween(
                'daily_activity_slaughter_houses.tanggal',
                [$startDate, $endDate]
            )
            ->selectRaw('
                SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah,
                COUNT(DISTINCT daily_activity_slaughter_house_id) as total_activity
            ')
            ->first();

        $totalKgSlaughterHouse = $summarySlaughterHouse->total_kg ?? 0;
        $totalRupiahSlaughterHouse = $summarySlaughterHouse->total_rupiah ?? 0;
        $totalActivitySlaughterHouse = $summarySlaughterHouse->total_activity ?? 0;

        $totalKg =
            $totalKgBorongan +
            $totalKgHarian +
            $totalKgFurther +
            $totalKgSlaughterHouse;

        $totalRupiah =
            $totalRupiahBorongan +
            $totalRupiahSlaughterHouse;

        $totalActivity =
            $totalActivityBorongan +
            $totalActivityHarian +
            $totalActivitySlaughterHouse;

        $totalKgWithRupiah =
            $totalKgBorongan +
            $totalKgSlaughterHouse;

        if ($totalKgWithRupiah > 0) {
            $averageHargaKg = $totalRupiah / $totalKgWithRupiah;
        } else {
            $averageHargaKg = 0;
        }

        $totalCostCenter = CostCenter::query()->count();

        $inputCostCenters = [];

        $dailyActivities = DailyActivity::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        foreach ($dailyActivities as $activity) {
            $inputCostCenters[] = $activity->cost_center_id;
        }

        $dailyProductionDetails = DailyProductionDetail::query()
            ->join(
                'daily_productions',
                'daily_productions.id',
                '=',
                'daily_production_details.daily_production_id'
            )
            ->whereBetween(
                'daily_productions.tanggal',
                [$startDate, $endDate]
            )
            ->get();

        foreach ($dailyProductionDetails as $production) {
            $inputCostCenters[] = $production->cost_center_id;
        }

        $dailyActivityFurther = DailyActivityFurther::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        foreach ($dailyActivityFurther as $activity) {
            $inputCostCenters[] = $activity->cost_center_id;
        }

        $dailyActivitySlaughterHouse = DailyActivitySlaughterHouse::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        foreach ($dailyActivitySlaughterHouse as $activity) {
            $inputCostCenters[] = $activity->cost_center_id;
        }

        $inputCostCenters = array_unique($inputCostCenters);

        $inputCostCenter = count($inputCostCenters);

        if ($totalCostCenter > 0) {
            $dailyActivityProgress =
                ($inputCostCenter / $totalCostCenter) * 100;
        } else {
            $dailyActivityProgress = 0;
        }

        $departments = Department::orderBy('name')->get();

        if ($departmentId) {

            $chartMode = 'cost_center';

            $entities = CostCenter::where(
                'department_id',
                $departmentId
            )
            ->orderBy('name')
            ->get();

            $borongan = DailyActivityDetail::query()
                ->join(
                    'daily_activities',
                    'daily_activities.id',
                    '=',
                    'daily_activity_details.daily_activity_id'
                )
                ->where(
                    'daily_activities.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_activities.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activities.cost_center_id as entity_id,
                    SUM(daily_activity_details.total_kg) as total_kg,
                    SUM(daily_activity_details.total_harga) as total_rupiah
                ')
                ->groupBy('daily_activities.cost_center_id')
                ->get();

            $harian = DailyProductionDetail::query()
                ->join(
                    'daily_productions',
                    'daily_productions.id',
                    '=',
                    'daily_production_details.daily_production_id'
                )
                ->where(
                    'daily_productions.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_productions.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_productions.cost_center_id as entity_id,
                    SUM(daily_production_details.total_kg) as total_kg,
                    SUM(daily_production_details.total_harga) as total_rupiah
                ')
                ->groupBy('daily_productions.cost_center_id')
                ->get();

            $further = DailyActivityDetailFurther::query()
                ->join(
                    'daily_activity_furthers',
                    'daily_activity_furthers.id',
                    '=',
                    'daily_activity_detail_furthers.daily_activity_further_id'
                )
                ->where(
                    'daily_activity_furthers.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_furthers.cost_center_id as entity_id,
                    SUM(daily_activity_detail_furthers.total_kg) as total_kg
                ')
                ->groupBy('daily_activity_furthers.cost_center_id')
                ->get();

            $slaughter = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->where(
                    'daily_activity_slaughter_houses.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_activity_slaughter_houses.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.cost_center_id as entity_id,
                    SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                    SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah
                ')
                ->groupBy('daily_activity_slaughter_houses.cost_center_id')
                ->get();

            $trendRows = [];

            $boronganTrend = DailyActivityDetail::query()
                ->join(
                    'daily_activities',
                    'daily_activities.id',
                    '=',
                    'daily_activity_details.daily_activity_id'
                )
                ->where(
                    'daily_activities.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_activities.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activities.cost_center_id as entity_id,
                    daily_activities.tanggal as tanggal,
                    SUM(daily_activity_details.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_activities.cost_center_id',
                    'daily_activities.tanggal'
                )
                ->get();

            foreach ($boronganTrend as $row) {
                $trendRows[] = $row;
            }

            $harianTrend = DailyProductionDetail::query()
                ->join(
                    'daily_productions',
                    'daily_productions.id',
                    '=',
                    'daily_production_details.daily_production_id'
                )
                ->where(
                    'daily_productions.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_productions.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_productions.cost_center_id as entity_id,
                    daily_productions.tanggal as tanggal,
                    SUM(daily_production_details.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_productions.cost_center_id',
                    'daily_productions.tanggal'
                )
                ->get();

            foreach ($harianTrend as $row) {
                $trendRows[] = $row;
            }

            $furtherTrend = DailyActivityDetailFurther::query()
                ->join(
                    'daily_activity_furthers',
                    'daily_activity_furthers.id',
                    '=',
                    'daily_activity_detail_furthers.daily_activity_further_id'
                )
                ->where(
                    'daily_activity_furthers.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_furthers.cost_center_id as entity_id,
                    daily_activity_furthers.tanggal as tanggal,
                    SUM(daily_activity_detail_furthers.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_activity_furthers.cost_center_id',
                    'daily_activity_furthers.tanggal'
                )
                ->get();

            foreach ($furtherTrend as $row) {
                $trendRows[] = $row;
            }

            $slaughterTrend = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->where(
                    'daily_activity_slaughter_houses.department_id',
                    $departmentId
                )
                ->whereBetween(
                    'daily_activity_slaughter_houses.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.cost_center_id as entity_id,
                    daily_activity_slaughter_houses.tanggal as tanggal,
                    SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_activity_slaughter_houses.cost_center_id',
                    'daily_activity_slaughter_houses.tanggal'
                )
                ->get();

            foreach ($slaughterTrend as $row) {
                $trendRows[] = $row;
            }

            $notInputDailyActivity = CostCenter::query()
                ->where(
                    'department_id',
                    $departmentId
                )
                ->whereNotIn(
                    'id',
                    $inputCostCenters
                )
                ->with('department')
                ->get();

        } else {

            $chartMode = 'department';

            $entities = $departments;

            $borongan = DailyActivityDetail::query()
                ->join(
                    'daily_activities',
                    'daily_activities.id',
                    '=',
                    'daily_activity_details.daily_activity_id'
                )
                ->whereBetween(
                    'daily_activities.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activities.department_id as entity_id,
                    SUM(daily_activity_details.total_kg) as total_kg,
                    SUM(daily_activity_details.total_harga) as total_rupiah
                ')
                ->groupBy('daily_activities.department_id')
                ->get();

            $harian = DailyProductionDetail::query()
                ->join(
                    'daily_productions',
                    'daily_productions.id',
                    '=',
                    'daily_production_details.daily_production_id'
                )
                ->whereBetween(
                    'daily_productions.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_productions.department_id as entity_id,
                    SUM(daily_production_details.total_kg) as total_kg,
                    SUM(daily_production_details.total_harga) as total_rupiah
                ')
                ->groupBy('daily_productions.department_id')
                ->get();

            $further = DailyActivityDetailFurther::query()
                ->join(
                    'daily_activity_furthers',
                    'daily_activity_furthers.id',
                    '=',
                    'daily_activity_detail_furthers.daily_activity_further_id'
                )
                ->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_furthers.department_id as entity_id,
                    SUM(daily_activity_detail_furthers.total_kg) as total_kg
                ')
                ->groupBy('daily_activity_furthers.department_id')
                ->get();

            $slaughter = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->whereBetween(
                    'daily_activity_slaughter_houses.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.department_id as entity_id,
                    SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                    SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah
                ')
                ->groupBy('daily_activity_slaughter_houses.department_id')
                ->get();

            $trendRows = [];

            $boronganTrend = DailyActivityDetail::query()
                ->join(
                    'daily_activities',
                    'daily_activities.id',
                    '=',
                    'daily_activity_details.daily_activity_id'
                )
                ->whereBetween(
                    'daily_activities.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activities.department_id as entity_id,
                    daily_activities.tanggal as tanggal,
                    SUM(daily_activity_details.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_activities.department_id',
                    'daily_activities.tanggal'
                )
                ->get();

            foreach ($boronganTrend as $row) {
                $trendRows[] = $row;
            }

            $harianTrend = DailyProductionDetail::query()
                ->join(
                    'daily_productions',
                    'daily_productions.id',
                    '=',
                    'daily_production_details.daily_production_id'
                )
                ->whereBetween(
                    'daily_productions.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_productions.department_id as entity_id,
                    daily_productions.tanggal as tanggal,
                    SUM(daily_production_details.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_productions.department_id',
                    'daily_productions.tanggal'
                )
                ->get();

            foreach ($harianTrend as $row) {
                $trendRows[] = $row;
            }

            $furtherTrend = DailyActivityDetailFurther::query()
                ->join(
                    'daily_activity_furthers',
                    'daily_activity_furthers.id',
                    '=',
                    'daily_activity_detail_furthers.daily_activity_further_id'
                )
                ->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_furthers.department_id as entity_id,
                    daily_activity_furthers.tanggal as tanggal,
                    SUM(daily_activity_detail_furthers.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_activity_furthers.department_id',
                    'daily_activity_furthers.tanggal'
                )
                ->get();

            foreach ($furtherTrend as $row) {
                $trendRows[] = $row;
            }

            $slaughterTrend = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->whereBetween(
                    'daily_activity_slaughter_houses.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.department_id as entity_id,
                    daily_activity_slaughter_houses.tanggal as tanggal,
                    SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg
                ')
                ->groupBy(
                    'daily_activity_slaughter_houses.department_id',
                    'daily_activity_slaughter_houses.tanggal'
                )
                ->get();

            foreach ($slaughterTrend as $row) {
                $trendRows[] = $row;
            }

            $notInputDailyActivity = CostCenter::query()
                ->whereNotIn(
                    'id',
                    $inputCostCenters
                )
                ->with('department')
                ->get();
        }

        $chartLabels = [];

        foreach ($entities as $entity) {
            $chartLabels[] = $entity->name;
        }

        $chartBorongan = [];
        $chartHarian = [];
        $chartFurther = [];
        $chartSlaughter = [];

        foreach ($entities as $entity) {

            $boronganData = $borongan
                ->where('entity_id', $entity->id)
                ->first();

            if ($boronganData) {
                $chartBorongan[] = (float) $boronganData->total_kg;
            } else {
                $chartBorongan[] = 0;
            }

            $harianData = $harian
                ->where('entity_id', $entity->id)
                ->first();

            if ($harianData) {
                $chartHarian[] = (float) $harianData->total_kg;
            } else {
                $chartHarian[] = 0;
            }

            $furtherData = $further
                ->where('entity_id', $entity->id)
                ->first();

            if ($furtherData) {
                $chartFurther[] = (float) $furtherData->total_kg;
            } else {
                $chartFurther[] = 0;
            }

            $slaughterData = $slaughter
                ->where('entity_id', $entity->id)
                ->first();

            if ($slaughterData) {
                $chartSlaughter[] = (float) $slaughterData->total_kg;
            } else {
                $chartSlaughter[] = 0;
            }
        }

        $chartDatasets = [
            [
                'label' => 'Karyawan Borongan',
                'data' => $chartBorongan,
            ],
            [
                'label' => 'Karyawan Harian',
                'data' => $chartHarian,
            ],
            [
                'label' => 'Further Processing',
                'data' => $chartFurther,
            ],
            [
                'label' => 'Slaughter House',
                'data' => $chartSlaughter,
            ],
        ];

        $summaryTable = [];

        foreach ($entities as $entity) {

            $dataBorongan = $borongan
                ->where('entity_id', $entity->id)
                ->first();

            $dataHarian = $harian
                ->where('entity_id', $entity->id)
                ->first();

            $dataFurther = $further
                ->where('entity_id', $entity->id)
                ->first();

            $dataSlaughter = $slaughter
                ->where('entity_id', $entity->id)
                ->first();

            if ($dataBorongan) {
                $kgBorongan = (float) $dataBorongan->total_kg;
                $rupiahBorongan = (float) $dataBorongan->total_rupiah;
            } else {
                $kgBorongan = 0;
                $rupiahBorongan = 0;
            }

            if ($dataHarian) {
                $kgHarian = (float) $dataHarian->total_kg;
            } else {
                $kgHarian = 0;
            }

            if ($dataFurther) {
                $kgFurther = (float) $dataFurther->total_kg;
            } else {
                $kgFurther = 0;
            }

            if ($dataSlaughter) {
                $kgSlaughter = (float) $dataSlaughter->total_kg;
                $rupiahSlaughter = (float) $dataSlaughter->total_rupiah;
            } else {
                $kgSlaughter = 0;
                $rupiahSlaughter = 0;
            }

            $totalKgEntity =
                $kgBorongan +
                $kgHarian +
                $kgFurther +
                $kgSlaughter;

            $rupiahEligibleKg =
                $kgBorongan +
                $kgSlaughter;

            $totalRupiahEntity =
                $rupiahBorongan +
                $rupiahSlaughter;

            if ($rupiahEligibleKg > 0) {
                $hargaPerKg =
                    $totalRupiahEntity / $rupiahEligibleKg;
            } else {
                $hargaPerKg = null;
            }

            $summaryTable[] = (object) [
                'id' => $entity->id,
                'name' => $entity->name,
                'total_kg' => $totalKgEntity,
                'total_rupiah' => $rupiahEligibleKg > 0
                    ? $totalRupiahEntity
                    : null,
                'harga_per_kg' => $hargaPerKg,
            ];
        }

        $byEntityDate = [];

        foreach ($trendRows as $row) {

            $tanggal = Carbon::parse($row->tanggal)
                ->format('Y-m-d');

            $key = $row->entity_id . '|' . $tanggal;

            if (isset($byEntityDate[$key])) {
                $byEntityDate[$key] += (float) $row->total_kg;
            } else {
                $byEntityDate[$key] = (float) $row->total_kg;
            }
        }

        $trendLabels = [];

        $periodCursor = $startDate->copy();

        while ($periodCursor->lte($endDate)) {

            $trendLabels[] =
                $periodCursor->translatedFormat('d M');

            $periodCursor->addDay();
        }

        if ($chartMode === 'cost_center') {

            $trendDatasets = [];

            foreach ($entities as $entity) {

                $data = [];

                $cursor = $startDate->copy();

                while ($cursor->lte($endDate)) {

                    $tanggal = $cursor->format('Y-m-d');

                    $key = $entity->id . '|' . $tanggal;

                    if (isset($byEntityDate[$key])) {
                        $data[] = (float) $byEntityDate[$key];
                    } else {
                        $data[] = 0;
                    }

                    $cursor->addDay();
                }

                $trendDatasets[] = [
                    'label' => $entity->name,
                    'data' => $data,
                ];
            }

        } else {

            $boronganTrend = DailyActivityDetail::query()
                ->join(
                    'daily_activities',
                    'daily_activities.id',
                    '=',
                    'daily_activity_details.daily_activity_id'
                )
                ->whereBetween(
                    'daily_activities.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activities.tanggal as tanggal,
                    SUM(daily_activity_details.total_kg) as total_kg
                ')
                ->groupBy('daily_activities.tanggal')
                ->get();

            $harianTrend = DailyProductionDetail::query()
                ->join(
                    'daily_productions',
                    'daily_productions.id',
                    '=',
                    'daily_production_details.daily_production_id'
                )
                ->whereBetween(
                    'daily_productions.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_productions.tanggal as tanggal,
                    SUM(daily_production_details.total_kg) as total_kg
                ')
                ->groupBy('daily_productions.tanggal')
                ->get();

            $furtherTrend = DailyActivityDetailFurther::query()
                ->join(
                    'daily_activity_furthers',
                    'daily_activity_furthers.id',
                    '=',
                    'daily_activity_detail_furthers.daily_activity_further_id'
                )
                ->whereBetween(
                    'daily_activity_furthers.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_furthers.tanggal as tanggal,
                    SUM(daily_activity_detail_furthers.total_kg) as total_kg
                ')
                ->groupBy('daily_activity_furthers.tanggal')
                ->get();

            $slaughterTrend = DailyActivityDetailSlaughterHouse::query()
                ->join(
                    'daily_activity_slaughter_houses',
                    'daily_activity_slaughter_houses.id',
                    '=',
                    'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
                )
                ->whereBetween(
                    'daily_activity_slaughter_houses.tanggal',
                    [$startDate, $endDate]
                )
                ->selectRaw('
                    daily_activity_slaughter_houses.tanggal as tanggal,
                    SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg
                ')
                ->groupBy('daily_activity_slaughter_houses.tanggal')
                ->get();

            $boronganTrendData = [];
            $harianTrendData = [];
            $furtherTrendData = [];
            $slaughterTrendData = [];

            foreach ($boronganTrend as $row) {

                $tanggal = Carbon::parse($row->tanggal)
                    ->format('Y-m-d');

                $boronganTrendData[$tanggal] =
                    (float) $row->total_kg;
            }

            foreach ($harianTrend as $row) {

                $tanggal = Carbon::parse($row->tanggal)
                    ->format('Y-m-d');

                $harianTrendData[$tanggal] =
                    (float) $row->total_kg;
            }

            foreach ($furtherTrend as $row) {

                $tanggal = Carbon::parse($row->tanggal)
                    ->format('Y-m-d');

                $furtherTrendData[$tanggal] =
                    (float) $row->total_kg;
            }

            foreach ($slaughterTrend as $row) {

                $tanggal = Carbon::parse($row->tanggal)
                    ->format('Y-m-d');

                $slaughterTrendData[$tanggal] =
                    (float) $row->total_kg;
            }

            $boronganData = [];
            $harianData = [];
            $furtherData = [];
            $slaughterData = [];

            $cursor = $startDate->copy();

            while ($cursor->lte($endDate)) {

                $tanggal = $cursor->format('Y-m-d');

                if (isset($boronganTrendData[$tanggal])) {
                    $boronganData[] =
                        $boronganTrendData[$tanggal];
                } else {
                    $boronganData[] = 0;
                }

                if (isset($harianTrendData[$tanggal])) {
                    $harianData[] =
                        $harianTrendData[$tanggal];
                } else {
                    $harianData[] = 0;
                }

                if (isset($furtherTrendData[$tanggal])) {
                    $furtherData[] =
                        $furtherTrendData[$tanggal];
                } else {
                    $furtherData[] = 0;
                }

                if (isset($slaughterTrendData[$tanggal])) {
                    $slaughterData[] =
                        $slaughterTrendData[$tanggal];
                } else {
                    $slaughterData[] = 0;
                }

                $cursor->addDay();
            }

            $trendDatasets = [
                [
                    'label' => 'Karyawan Borongan',
                    'data' => $boronganData,
                ],
                [
                    'label' => 'Karyawan Harian',
                    'data' => $harianData,
                ],
                [
                    'label' => 'Further Processing',
                    'data' => $furtherData,
                ],
                [
                    'label' => 'Slaughter House',
                    'data' => $slaughterData,
                ],
            ];
        }

        $employeeAttendance = Attendance::query()
            ->whereBetween(
                'date',
                [$startDate, $endDate]
            )
            ->pluck('employee_id');

        $notAttendance = Employee::query();

        if ($departmentId) {
            $notAttendance->where(
                'department_id',
                $departmentId
            );
        }

        if ($costCenterId) {
            $notAttendance->where(
                'cost_center_id',
                $costCenterId
            );
        }

        $notAttendance = $notAttendance
            ->whereNotIn(
                'id',
                $employeeAttendance
            )
            ->get();

        $recentActivitiesSosis = DailyActivity::with([
            'costCenter',
            'psGroup',
            'employee'
        ])
        ->latest()
        ->take(10)
        ->get();

        $recentActivitiesFurther = DailyActivityFurther::with([
            'costCenter',
            'psGroup',
            'employee'
        ])
        ->latest()
        ->take(10)
        ->get();

        $recentActivitiesSlaughterHouse = DailyActivitySlaughterHouse::with([
            'costCenter',
            'psGroup',
            'employee'
        ])
        ->latest()
        ->take(10)
        ->get();

        $recentActivities = [];

        foreach ($recentActivitiesSosis as $activity) {
            $recentActivities[] = $activity;
        }

        foreach ($recentActivitiesFurther as $activity) {
            $recentActivities[] = $activity;
        }

        foreach ($recentActivitiesSlaughterHouse as $activity) {
            $recentActivities[] = $activity;
        }

        usort($recentActivities, function ($a, $b) {
            return $b->created_at <=> $a->created_at;
        });

        $recentActivities = array_slice(
            $recentActivities,
            0,
            10
        );

        return view(
            'pages.dashboard.general-manager',
            compact(
                'startDate',
                'endDate',
                'totalEmployee',
                'hadir',
                'izin',
                'sakit',
                'alpha',
                'attendanceProgress',
                'totalActivity',
                'totalKg',
                'totalRupiah',
                'averageHargaKg',
                'dailyActivityProgress',
                'totalKgBorongan',
                'totalKgHarian',
                'totalKgFurther',
                'totalKgSlaughterHouse',
                'notAttendance',
                'notInputDailyActivity',
                'recentActivities',
                'departments',
                'chartMode',
                'chartLabels',
                'chartDatasets',
                'summaryTable',
                'trendLabels',
                'trendDatasets',
            )
        );
    }
    
    private function managerDashboard(Request $request)
    {
        $today = Carbon::today();

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)
            : $today->copy()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)
            : $today;

        $departmentId = auth()->user()->department_id;
        $costCenterId = $request->cost_center_id;

        $attendanceQuery = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });

        $employeeQuery = Employee::query()
            ->where('department_id', $departmentId);

        $totalEmployee = (clone $employeeQuery)->count();

        $hadir = (clone $attendanceQuery)->where('status', 'Hadir')->count();
        $izin  = (clone $attendanceQuery)->where('status', 'Izin')->count();
        $sakit = (clone $attendanceQuery)->where('status', 'Sakit')->count();
        $alpha = (clone $attendanceQuery)->where('status', 'Alpha')->count();

        $attendanceProgress = $totalEmployee > 0
            ? (($hadir + $izin + $sakit + $alpha) / $totalEmployee) * 100
            : 0;


        $dailyActivitySosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->where('daily_activities.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activities.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate]);

        $summarySosis = (clone $dailyActivitySosis)
            ->selectRaw("
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah,
                COUNT(DISTINCT daily_activity_id) as total_activity
            ")
            ->first();

        $totalKgSosis      = $summarySosis->total_kg ?? 0;
        $totalRupiahSosis  = $summarySosis->total_rupiah ?? 0;
        $totalActivitySosis = $summarySosis->total_activity ?? 0;

        $averageHargaKgSosis = $totalKgSosis > 0
            ? $totalRupiahSosis / $totalKgSosis
            : 0;

        $dailyActivityFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activity_furthers.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activity_furthers.tanggal', [$startDate, $endDate]);

        $summaryFurther = (clone $dailyActivityFurther)
            ->selectRaw("
                SUM(total_kg) as total_kg,
                COUNT(DISTINCT daily_activity_further_id) as total_activity
            ")
            ->first();

        $totalKgFurther      = $summaryFurther->total_kg ?? 0;
        $totalActivityFurther = $summaryFurther->total_activity ?? 0;
        // further gak punya total_harga, jadi gak ada total_rupiah / averageHargaKg


        $totalCostCenter = CostCenter::query()
            ->where('department_id', $departmentId)
            ->count();

        $inputCostCenterSosis = DailyActivity::query()
            ->where('department_id', $departmentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->distinct('cost_center_id')
            ->count('cost_center_id');

        $dailyActivityProgressSosis = $totalCostCenter > 0
            ? ($inputCostCenterSosis / $totalCostCenter) * 100
            : 0;

        $inputCostCenterFurther = DailyActivityFurther::query()
            ->where('department_id', $departmentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->distinct('cost_center_id')
            ->count('cost_center_id');

        $dailyActivityProgressFurther = $totalCostCenter > 0
            ? ($inputCostCenterFurther / $totalCostCenter) * 100
            : 0;

        $departmentSummarySosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('departments', 'departments.id', '=', 'daily_activities.department_id')
            ->selectRaw("
                departments.id,
                departments.name,
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah
            ")
            ->where('daily_activities.department_id', $departmentId)
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        foreach ($departmentSummarySosis as $department) {
            $department->harga_per_kg = $department->total_kg > 0
                ? $department->total_rupiah / $department->total_kg
                : 0;
        }

        $departmentSummaryFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('departments', 'departments.id', '=', 'daily_activity_furthers.department_id')
            ->selectRaw("
                departments.id,
                departments.name,
                SUM(total_kg) as total_kg
            ")
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->whereBetween('daily_activity_furthers.tanggal', [$startDate, $endDate])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        $costCenterInputSosis = DailyActivity::query()
            ->where('department_id', $departmentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('cost_center_id');

        $notInputDailyActivitySosis = CostCenter::query()
            ->where('department_id', $departmentId)
            ->whereNotIn('id', $costCenterInputSosis)
            ->with('department')
            ->get();

        $costCenterInputFurther = DailyActivityFurther::query()
            ->where('department_id', $departmentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('cost_center_id');

        $notInputDailyActivityFurther = CostCenter::query()
            ->where('department_id', $departmentId)
            ->whereNotIn('id', $costCenterInputFurther)
            ->with('department')
            ->get();

        $employeeAttendance = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->pluck('employee_id');

        $notAttendance = Employee::query()
            ->where('department_id', $departmentId)
            ->whereNotIn('id', $employeeAttendance)
            ->get();

        $recentActivitiesSosis = DailyActivity::with(['costCenter', 'psGroup', 'employee'])
            ->where('department_id', $departmentId)
            ->latest()
            ->take(10)
            ->get();

        $recentActivitiesFurther = DailyActivityFurther::with(['costCenter', 'psGroup', 'employee', 'line'])
            ->where('department_id', $departmentId)
            ->latest()
            ->take(10)
            ->get();

        $costCenters = CostCenter::where('department_id', $departmentId)->get();

        $outputTrendSosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->where('daily_activities.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activities.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate])
            ->selectRaw("
                daily_activities.tanggal as tanggal,
                SUM(daily_activity_details.total_kg) as total_kg
            ")
            ->groupBy('daily_activities.tanggal')
            ->orderBy('daily_activities.tanggal')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->tanggal)->format('Y-m-d');
            });


        $outputTrendFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activity_furthers.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activity_furthers.tanggal', [$startDate, $endDate])
            ->selectRaw("
                daily_activity_furthers.tanggal as tanggal,
                SUM(daily_activity_detail_furthers.total_kg) as total_kg
            ")
            ->groupBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.tanggal')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->tanggal)->format('Y-m-d');
            });


        $dailyActivitySlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->where('daily_activity_slaughter_houses.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activity_slaughter_houses.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$startDate, $endDate]);

        $summarySlaughterHouse = (clone $dailyActivitySlaughterHouse)
            ->selectRaw("
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah,
                COUNT(DISTINCT daily_activity_slaughter_house_id) as total_activity
            ")
            ->first();

        $totalKgSlaughterHouse = $summarySlaughterHouse->total_kg ?? 0;
        $totalRupiahSlaughterHouse = $summarySlaughterHouse->total_rupiah ?? 0;
        $totalActivitySlaughterHouse = $summarySlaughterHouse->total_activity ?? 0;

        $averageHargaKgSlaughterHouse = $totalKgSlaughterHouse > 0
            ? $totalRupiahSlaughterHouse / $totalKgSlaughterHouse
            : 0;

        $inputCostCenterSlaughterHouse = DailyActivitySlaughterHouse::query()
            ->where('department_id', $departmentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->distinct('cost_center_id')
            ->count('cost_center_id');

        $dailyActivityProgressSlaughterHouse = $totalCostCenter > 0
            ? ($inputCostCenterSlaughterHouse / $totalCostCenter) * 100
            : 0;
        
        $departmentSummarySlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->join(
                'departments',
                'departments.id',
                '=',
                'daily_activity_slaughter_houses.department_id'
            )
            ->selectRaw("
                departments.id,
                departments.name,
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah
            ")
            ->where('daily_activity_slaughter_houses.department_id', $departmentId)
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$startDate, $endDate])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        foreach ($departmentSummarySlaughterHouse as $department) {
            $department->harga_per_kg = $department->total_kg > 0
                ? $department->total_rupiah / $department->total_kg
                : 0;
        }

        $costCenterInputSlaughterHouse = DailyActivitySlaughterHouse::query()
            ->where('department_id', $departmentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('cost_center_id');

        $notInputDailyActivitySlaughterHouse = CostCenter::query()
            ->where('department_id', $departmentId)
            ->whereNotIn('id', $costCenterInputSlaughterHouse)
            ->with('department')
            ->get();

        $recentActivitiesSlaughterHouse = DailyActivitySlaughterHouse::with([
            'costCenter',
            'psGroup',
            'employee',
            'productGroup',
            'line'
        ])
            ->where('department_id', $departmentId)
            ->latest()
            ->take(10)
            ->get();

        $outputTrendSlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->where('daily_activity_slaughter_houses.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activity_slaughter_houses.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$startDate, $endDate])
            ->selectRaw("
                daily_activity_slaughter_houses.tanggal as tanggal,
                SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg
            ")
            ->groupBy('daily_activity_slaughter_houses.tanggal')
            ->orderBy('daily_activity_slaughter_houses.tanggal')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->tanggal)->format('Y-m-d');
            });

        $costCenterSummarySosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activities.cost_center_id')
            ->selectRaw("
                cost_centers.id,
                cost_centers.name,
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah
            ")
            ->where('daily_activities.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activities.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate])
            ->groupBy('cost_centers.id', 'cost_centers.name')
            ->orderBy('cost_centers.name')
            ->get();

        foreach ($costCenterSummarySosis as $costCenter) {
            $costCenter->harga_per_kg = $costCenter->total_kg > 0
                ? $costCenter->total_rupiah / $costCenter->total_kg
                : 0;
        }

        $costCenterSummaryFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_furthers.cost_center_id')
            ->selectRaw("
                cost_centers.id,
                cost_centers.name,
                SUM(total_kg) as total_kg
            ")
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activity_furthers.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activity_furthers.tanggal', [$startDate, $endDate])
            ->groupBy('cost_centers.id', 'cost_centers.name')
            ->orderBy('cost_centers.name')
            ->get();

        $costCenterSummarySlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join(
                'daily_activity_slaughter_houses',
                'daily_activity_slaughter_houses.id',
                '=',
                'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id'
            )
            ->join(
                'cost_centers',
                'cost_centers.id',
                '=',
                'daily_activity_slaughter_houses.cost_center_id'
            )
            ->selectRaw("
                cost_centers.id,
                cost_centers.name,
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah
            ")
            ->where('daily_activity_slaughter_houses.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_activity_slaughter_houses.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$startDate, $endDate])
            ->groupBy('cost_centers.id', 'cost_centers.name')
            ->orderBy('cost_centers.name')
            ->get();

        foreach ($costCenterSummarySlaughterHouse as $costCenter) {
            $costCenter->harga_per_kg = $costCenter->total_kg > 0
                ? $costCenter->total_rupiah / $costCenter->total_kg
                : 0;
        }

        $costCenterSummaryProductionHarian = DailyProductionDetail::query()
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_productions.cost_center_id')
            ->selectRaw("
                cost_centers.id,
                cost_centers.name,
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah
            ")
            ->where('daily_productions.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_productions.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_productions.tanggal', [$startDate, $endDate])
            ->groupBy('cost_centers.id', 'cost_centers.name')
            ->orderBy('cost_centers.name')
            ->get();

        foreach ($costCenterSummaryProductionHarian as $costCenter) {
            $costCenter->harga_per_kg = $costCenter->total_kg > 0
                ? $costCenter->total_rupiah / $costCenter->total_kg
                : 0;
        }

        $outputTrendProductionHarian = DailyProductionDetail::query()
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->where('daily_productions.department_id', $departmentId)
            ->when($costCenterId, function ($q) use ($costCenterId) {
                return $q->where('daily_productions.cost_center_id', $costCenterId);
            })
            ->whereBetween('daily_productions.tanggal', [$startDate, $endDate])
            ->selectRaw("
                daily_productions.tanggal as tanggal,
                SUM(daily_production_details.total_kg) as total_kg
            ")
            ->groupBy('daily_productions.tanggal')
            ->orderBy('daily_productions.tanggal')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->tanggal)->format('Y-m-d');
            });


        $trendLabels        = [];
        $trendOutputKgSosis   = [];
        $trendOutputKgFurther = [];
        $trendOutputKgSlaughterHouse = [];
        $trendOutputKgProductionHarian = [];

        $periodCursor = $startDate->copy();

        while ($periodCursor->lte($endDate)) {
            $key = $periodCursor->format('Y-m-d');

            $trendLabels[]          = $periodCursor->translatedFormat('d M');
            $trendOutputKgSosis[]   = (float) ($outputTrendSosis[$key]->total_kg ?? 0);
            $trendOutputKgFurther[] = (float) ($outputTrendFurther[$key]->total_kg ?? 0);
            $trendOutputKgSlaughterHouse[] = (float) (
                $outputTrendSlaughterHouse[$key]->total_kg ?? 0
            );
            $trendOutputKgProductionHarian[] = (float) ($outputTrendProductionHarian[$key]->total_kg ?? 0);

            $periodCursor->addDay();
        }
        

        return view('pages.dashboard.manager', compact(
            'startDate',
            'endDate',
            'totalEmployee',
            'hadir',
            'izin',
            'sakit',
            'alpha',
            'attendanceProgress',
            'totalActivitySosis',
            'totalKgSosis',
            'totalRupiahSosis',
            'averageHargaKgSosis',
            'totalActivityFurther',
            'totalKgFurther',
            'dailyActivityProgressSosis',
            'dailyActivityProgressFurther',
            'departmentSummarySosis',
            'departmentSummaryFurther',
            'notAttendance',
            'notInputDailyActivitySosis',
            'notInputDailyActivityFurther',
            'recentActivitiesSosis',
            'recentActivitiesFurther',
            'costCenters',
            'trendLabels',
            'trendOutputKgSosis',
            'trendOutputKgFurther',
            'totalActivitySlaughterHouse',
            'totalKgSlaughterHouse',
            'totalRupiahSlaughterHouse',
            'averageHargaKgSlaughterHouse',
            'dailyActivityProgressSlaughterHouse',
            'departmentSummarySlaughterHouse',
            'notInputDailyActivitySlaughterHouse',
            'recentActivitiesSlaughterHouse',
            'trendOutputKgSlaughterHouse',
            'costCenterSummarySosis',
            'costCenterSummaryFurther',
            'costCenterSummarySlaughterHouse',
            'costCenterSummaryProductionHarian',
            'trendOutputKgProductionHarian',
        ));
    }

    public function personaliaDashboard()
    {
        return view('pages.dashboard.personalia');
    }

    private function adminProductionDashboard()
    {
        $departmentId = auth()->user()->department_id;
        $today        = Carbon::today();

        // ===== 1. Cards ringkasan =====
        $totalKaryawan = Employee::where('department_id', $departmentId)->count();

        $attendanceToday = Attendance::query()
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->where('employees.department_id', $departmentId)
            ->whereDate('attendances.date', $today)
            ->selectRaw("
                SUM(CASE WHEN attendances.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN attendances.status IN ('izin','sakit','cuti') THEN 1 ELSE 0 END) as izin_sakit_cuti,
                SUM(CASE WHEN attendances.status = 'alfa' THEN 1 ELSE 0 END) as alfa
            ")
            ->first();

        $outputHariIniSosis   = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->where('daily_activities.department_id', $departmentId)
            ->whereDate('daily_activities.tanggal', $today)
            ->sum('daily_activity_details.total_kg');
        
        $outputHariIniSosisProduction = DailyProductionDetail::query()
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->where('daily_productions.department_id', $departmentId)
            ->whereDate('daily_productions.tanggal', $today)
            ->sum('daily_production_details.total_kg');

        $outputHariIniFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->whereDate('daily_activity_furthers.tanggal', $today)
            ->sum('daily_activity_detail_furthers.total_kg');
        
        $outputHariIniSlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->where('daily_activity_slaughter_houses.department_id', $departmentId)
            ->whereDate('daily_activity_slaughter_houses.tanggal', $today)
            ->sum('daily_activity_detail_slaughter_houses.total_kg');

        // ===== 2. Tren 7 hari (kehadiran & output) =====
        $startTrend = $today->copy()->subDays(6);

        $attendanceTrend = Attendance::query()
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->where('employees.department_id', $departmentId)
            ->whereBetween('attendances.date', [$startTrend, $today])
            ->selectRaw("
                attendances.date as tanggal,
                SUM(CASE WHEN attendances.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN attendances.status = 'alfa' THEN 1 ELSE 0 END) as alfa
            ")
            ->groupBy('attendances.date')
            ->orderBy('attendances.date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->tanggal)->format('Y-m-d'));

        $outputTrendSosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->where('daily_activities.department_id', $departmentId)
            ->whereBetween('daily_activities.tanggal', [$startTrend, $today])
            ->selectRaw("
                daily_activities.tanggal as tanggal,
                SUM(daily_activity_details.total_kg) as total_kg
            ")
            ->groupBy('daily_activities.tanggal')
            ->orderBy('daily_activities.tanggal')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->tanggal)->format('Y-m-d');
            });

        $outputTrendFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->whereBetween('daily_activity_furthers.tanggal', [$startTrend, $today])
            ->selectRaw("
                daily_activity_furthers.tanggal as tanggal,
                SUM(daily_activity_detail_furthers.total_kg) as total_kg
            ")
            ->groupBy('daily_activity_furthers.tanggal')
            ->orderBy('daily_activity_furthers.tanggal')
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->tanggal)->format('Y-m-d');
            });

        $outputTrendSosisProduction = DailyProductionDetail::query()
            ->join('daily_productions', 'daily_productions.id', '=', 'daily_production_details.daily_production_id')
            ->where('daily_productions.department_id', $departmentId)
            ->whereBetween('daily_productions.tanggal', [$startTrend, $today])
            ->selectRaw("
                daily_productions.tanggal as tanggal,
                SUM(daily_production_details.total_kg) as total_kg
            ")
            ->groupBy('daily_productions.tanggal')
            ->orderBy('daily_productions.tanggal')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->tanggal)->format('Y-m-d'));
        
        $outputTrendSlaughterHouse = DailyActivityDetailSlaughterHouse::query()
            ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
            ->where('daily_activity_slaughter_houses.department_id', $departmentId)
            ->whereBetween('daily_activity_slaughter_houses.tanggal', [$startTrend, $today])
            ->selectRaw("
                daily_activity_slaughter_houses.tanggal as tanggal,
                SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg
            ")
            ->groupBy('daily_activity_slaughter_houses.tanggal')
            ->orderBy('daily_activity_slaughter_houses.tanggal')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->tanggal)->format('Y-m-d'));


        $trendLabels        = [];
        $trendHadir         = [];
        $trendAlfa          = [];
        $trendOutputKgSosis   = [];
        $trendOutputKgSosisProduction = [];
        $trendOutputKgFurther = [];
        $trendOutputKgSlaughterHouse = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startTrend->copy()->addDays($i);
            $key  = $date->format('Y-m-d');

            $trendLabels[]          = $date->translatedFormat('D');
            $trendHadir[]           = (int) ($attendanceTrend[$key]->hadir ?? 0);
            $trendAlfa[]            = (int) ($attendanceTrend[$key]->alfa ?? 0);
            $trendOutputKgSosis[]   = (float) ($outputTrendSosis[$key]->total_kg ?? 0);
            $trendOutputKgSosisProduction[] = (float) ($outputTrendSosisProduction[$key]->total_kg ?? 0);
            $trendOutputKgFurther[] = (float) ($outputTrendFurther[$key]->total_kg ?? 0);
            $trendOutputKgSlaughterHouse[] = (float) ($outputTrendSlaughterHouse[$key]->total_kg ?? 0);
        }

        // ===== 3. Perlu perhatian: karyawan alfa hari ini =====
        $alfaHariIni = Attendance::query()
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->where('employees.department_id', $departmentId)
            ->whereDate('attendances.date', $today)
            ->where('attendances.status', 'alfa')
            ->select('employees.nik', 'employees.name')
            ->get();

        // ===== 4. Perlu perhatian: karyawan yang belum diabsen hari ini =====
        $belumDiabsen = Employee::where('department_id', $departmentId)
            ->whereDoesntHave('attendances', function ($q) use ($today) {
                $q->whereDate('date', $today);
            })
            ->count();

        // ===== 5. Ringkasan cost center hari ini =====
        $costCenterSummary = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->join('cost_centers', 'cost_centers.id', '=', 'daily_activities.cost_center_id')
            ->where('daily_activities.department_id', $departmentId)
            ->whereDate('daily_activities.tanggal', $today)
            ->selectRaw("
                cost_centers.id as cost_center_id,
                cost_centers.name as cost_center_name,
                SUM(daily_activity_details.total_kg) as total_kg,
                SUM(daily_activity_details.total_harga) as total_rupiah
            ")
            ->groupBy('cost_centers.id', 'cost_centers.name')
            ->orderBy('cost_centers.name')
            ->get()
            ->map(function ($row) {
                $row->harga_per_kg = $row->total_kg > 0
                    ? $row->total_rupiah / $row->total_kg
                    : 0;

                return $row;
            });
            
            $costCenterSummaryFurther = DailyActivityDetailFurther::query()
                ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
                ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_furthers.cost_center_id')
                ->where('daily_activity_furthers.department_id', $departmentId)
                ->whereDate('daily_activity_furthers.tanggal', $today)
                ->selectRaw("
                    cost_centers.id as cost_center_id,
                    cost_centers.name as cost_center_name,
                    SUM(daily_activity_detail_furthers.total_kg) as total_kg
                ")
                ->groupBy('cost_centers.id', 'cost_centers.name')
                ->orderBy('cost_centers.name')
                ->get();
            
            $costCenterSummarySlaughterHouse = DailyActivityDetailSlaughterHouse::query()
                ->join('daily_activity_slaughter_houses', 'daily_activity_slaughter_houses.id', '=', 'daily_activity_detail_slaughter_houses.daily_activity_slaughter_house_id')
                ->join('cost_centers', 'cost_centers.id', '=', 'daily_activity_slaughter_houses.cost_center_id')
                ->where('daily_activity_slaughter_houses.department_id', $departmentId)
                ->whereDate('daily_activity_slaughter_houses.tanggal', $today)
                ->selectRaw("
                    cost_centers.id as cost_center_id,
                    cost_centers.name as cost_center_name,
                    SUM(daily_activity_detail_slaughter_houses.total_kg) as total_kg,
                    SUM(daily_activity_detail_slaughter_houses.total_harga) as total_rupiah
                ")
                ->groupBy('cost_centers.id', 'cost_centers.name')
                ->orderBy('cost_centers.name')
                ->get()
                ->map(function ($row) {
                    $row->harga_per_kg = $row->total_kg > 0
                        ? $row->total_rupiah / $row->total_kg
                        : 0;

                    return $row;
                });

        return view('pages.dashboard.admin-production', compact(
            'totalKaryawan',
            'attendanceToday',
            'outputHariIniSosis',
            'outputHariIniFurther',
            'trendLabels',
            'trendHadir',
            'trendAlfa',
            'trendOutputKgSosis',
            'trendOutputKgFurther',
            'alfaHariIni',
            'belumDiabsen',
            'costCenterSummary',
            'costCenterSummaryFurther',
            'trendOutputKgSosisProduction',
            'outputHariIniSosisProduction',
            'outputHariIniSlaughterHouse',
            'trendOutputKgSlaughterHouse',
            'costCenterSummarySlaughterHouse',
                ));
    }

    // private function financeDashboard()
    // {
    //    $now = Carbon::now();

    //     $data = [
    //         // Dari payroll_simulations
    //         'totalPayroll' => PayrollBulanan::where('period_month', $now->month)
    //             ->where('period_year', $now->year)
    //             ->sum('net_salary'),

    //         'draftPayroll'  => PayrollBulanan::where('status', 'DRAFT')->count(),
    //         'finalPayroll'  => PayrollBulanan::where('status', 'FINAL')->count(),

    //         'recentPayrolls' => PayrollBulanan::with('employee')
    //             ->latest()->take(10)->get(),

    //         'payrollStatusChart' => [
    //             'draft' => PayrollBulanan::where('status', 'DRAFT')->count(),
    //             'final' => PayrollBulanan::where('status', 'FINAL')->count(),
    //         ],
    //         // Bonus, deduction, overtime tetap dari model masing-masing
    //         'totalBonus'     => Bonus::where('status', 'APPROVED')->sum('amount'),
    //         'totalDeduction' => Deduction::where('status', 'APPROVED')->sum('amount'),
    //         'totalOvertime'  => Overtime::where('status', 'APPROVED')->sum('amount'),

    //         'payrollComposition' => [
    //             'bonus'     => Bonus::where('status', 'APPROVED')->sum('amount'),
    //             'deduction' => Deduction::where('status', 'APPROVED')->sum('amount'),
    //             'overtime'  => Overtime::where('status', 'APPROVED')->sum('amount'),
    //         ],
    //     ];

    //     return view('pages.dashboard.finance', $data);
    // }
}
