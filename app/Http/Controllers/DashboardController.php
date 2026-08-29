<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\CostCenter;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\DailyActivityDetailFurther;
use App\Models\DailyActivityFurther;
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

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)
            : $today->copy()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)
            : $today;

        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

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

        $hadir = (clone $attendanceQuery)->where('status', 'Hadir')->count();
        $izin  = (clone $attendanceQuery)->where('status', 'Izin')->count();
        $sakit = (clone $attendanceQuery)->where('status', 'Sakit')->count();
        $alpha = (clone $attendanceQuery)->where('status', 'Alpha')->count();

        $attendanceProgress = $totalEmployee > 0
            ? (($hadir + $izin + $sakit + $alpha) / $totalEmployee) * 100
            : 0;

        $summarySosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate])
            ->selectRaw("
                SUM(total_kg) as total_kg,
                SUM(total_harga) as total_rupiah,
                COUNT(DISTINCT daily_activity_id) as total_activity
            ")
            ->first();

        $totalKgSosis     = $summarySosis->total_kg ?? 0;
        $totalRupiahSosis = $summarySosis->total_rupiah ?? 0;
        $totalActivity    = $summarySosis->total_activity ?? 0;

        $summaryFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->whereBetween('daily_activity_furthers.tanggal', [$startDate, $endDate])
            ->selectRaw("SUM(total_kg) as total_kg")
            ->first();

        $totalKgFurther = $summaryFurther->total_kg ?? 0;

        $totalKg     = $totalKgSosis + $totalKgFurther;
        $totalRupiah = $totalRupiahSosis;

        $averageHargaKg = $totalKgSosis > 0
            ? $totalRupiahSosis / $totalKgSosis
            : 0;

        $totalCostCenter = CostCenter::query()->count();

        $inputCostCenterSosis = DailyActivity::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->distinct('cost_center_id')
            ->count('cost_center_id');

        $inputCostCenterFurther = DailyActivityFurther::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->distinct('cost_center_id')
            ->count('cost_center_id');

        $inputCostCenter = $inputCostCenterSosis + $inputCostCenterFurther;

        $dailyActivityProgress = $totalCostCenter > 0
            ? ($inputCostCenter / $totalCostCenter) * 100
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
            ->whereBetween('daily_activities.tanggal', [$startDate, $endDate])
            ->groupBy('departments.id', 'departments.name')
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
            ->whereBetween('daily_activity_furthers.tanggal', [$startDate, $endDate])
            ->groupBy('departments.id', 'departments.name')
            ->get();

        foreach ($departmentSummaryFurther as $department) {
            $department->total_rupiah = null;
            $department->harga_per_kg = null;
        }

        $departmentSummary = $departmentSummarySosis
            ->concat($departmentSummaryFurther)
            ->sortBy('name')
            ->values();

        $costCenterInputSosis = DailyActivity::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('cost_center_id');

        $costCenterInputFurther = DailyActivityFurther::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('cost_center_id');

        $costCenterInput = $costCenterInputSosis->merge($costCenterInputFurther);

        $notInputDailyActivity = CostCenter::query()
            ->whereNotIn('id', $costCenterInput)
            ->with('department')
            ->get();

        $employeeAttendance = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('employee_id');

        $notAttendance = Employee::query()
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->when($costCenterId, function ($q) use ($costCenterId) {
                $q->where('cost_center_id', $costCenterId);
            })
            ->whereNotIn('id', $employeeAttendance)
            ->get();

        $departments = Department::orderBy('name')->get();

        $recentActivitiesSosis = DailyActivity::with(['costCenter'])
            ->latest()
            ->take(10)
            ->get();

        $recentActivitiesFurther = DailyActivityFurther::with(['costCenter'])
            ->latest()
            ->take(10)
            ->get();

        $recentActivities = $recentActivitiesSosis
            ->concat($recentActivitiesFurther)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        $outputTrendSosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
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


        $trendLabels        = [];
        $trendOutputKgSosis   = [];
        $trendOutputKgFurther = [];

        $periodCursor = $startDate->copy();

        while ($periodCursor->lte($endDate)) {
            $key = $periodCursor->format('Y-m-d');

            $trendLabels[]          = $periodCursor->translatedFormat('d M');
            $trendOutputKgSosis[]   = (float) ($outputTrendSosis[$key]->total_kg ?? 0);
            $trendOutputKgFurther[] = (float) ($outputTrendFurther[$key]->total_kg ?? 0);

            $periodCursor->addDay();
        }

        return view('pages.dashboard.general-manager', compact(
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
            'notAttendance',
            'notInputDailyActivity',
            'recentActivities',
            'departments',
            'trendLabels',
            'trendOutputKgSosis',
            'trendOutputKgFurther',
        ));
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

        /*
        |--------------------------------------------------------------------------
        | Attendance (sama, gak kena branch)
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Daily Activity — Sosis
        |--------------------------------------------------------------------------
        */

        $dailyActivitySosis = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->where('daily_activities.department_id', $departmentId)
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

        /*
        |--------------------------------------------------------------------------
        | Daily Activity — Further
        |--------------------------------------------------------------------------
        */

        $dailyActivityFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->where('daily_activity_furthers.department_id', $departmentId)
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

        /*
        |--------------------------------------------------------------------------
        | Progress Daily Activity — Sosis
        |--------------------------------------------------------------------------
        */

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


        $trendLabels        = [];
        $trendOutputKgSosis   = [];
        $trendOutputKgFurther = [];

        $periodCursor = $startDate->copy();

        while ($periodCursor->lte($endDate)) {
            $key = $periodCursor->format('Y-m-d');

            $trendLabels[]          = $periodCursor->translatedFormat('d M');
            $trendOutputKgSosis[]   = (float) ($outputTrendSosis[$key]->total_kg ?? 0);
            $trendOutputKgFurther[] = (float) ($outputTrendFurther[$key]->total_kg ?? 0);

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

        $outputHariIniFurther = DailyActivityDetailFurther::query()
            ->join('daily_activity_furthers', 'daily_activity_furthers.id', '=', 'daily_activity_detail_furthers.daily_activity_further_id')
            ->where('daily_activity_furthers.department_id', $departmentId)
            ->whereDate('daily_activity_furthers.tanggal', $today)
            ->sum('daily_activity_detail_furthers.total_kg');

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

        $trendLabels        = [];
        $trendHadir         = [];
        $trendAlfa          = [];
        $trendOutputKgSosis   = [];
        $trendOutputKgFurther = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startTrend->copy()->addDays($i);
            $key  = $date->format('Y-m-d');

            $trendLabels[]          = $date->translatedFormat('D');
            $trendHadir[]           = (int) ($attendanceTrend[$key]->hadir ?? 0);
            $trendAlfa[]            = (int) ($attendanceTrend[$key]->alfa ?? 0);
            $trendOutputKgSosis[]   = (float) ($outputTrendSosis[$key]->total_kg ?? 0);
            $trendOutputKgFurther[] = (float) ($outputTrendFurther[$key]->total_kg ?? 0);
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
