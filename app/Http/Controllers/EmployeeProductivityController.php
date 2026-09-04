<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\DailyActivity;
use App\Models\DailyActivityFurther;
use App\Models\DailyActivitySlaughterHouse;
use Illuminate\Http\Request;

class EmployeeProductivityController extends Controller
{
   public function list(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        $department = Department::where('id', $departmentId)->firstOrFail();

        $employees = Employee::where('department_id', $departmentId)
            ->where('employee_status', 'borongan')
            ->orderBy('name')
            ->get();

        return view('pages.admin_production.employee_productivity.list', compact(
            'department',
            'employees',
        ));
    }

    public function generalManagerList(Request $request)
    {
        $query = Employee::query()
            ->where('employee_status', 'borongan');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->orderBy('name')->get();

        $departments = Department::orderBy('name')->get();

        return view('pages.general_manager.employee-productivity.list', compact(
            'employees',
            'departments',
        ));
    }

    public function managerList(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $managerDepartment = Department::find($managerDepartmentId);

        $employees = Employee::where('department_id', $managerDepartmentId)
            ->where('employee_status', 'borongan')
            ->orderBy('name')
            ->get();

        return view('pages.manager.employee-productivity.list', compact(
            'managerDepartment',
            'employees',
        ));
    }

    public function detail(Request $request, $employee_id)
    {
        $departmentId = auth()->user()->department_id;

        $employee = Employee::where('department_id', $departmentId)
            ->findOrFail($employee_id);

        $from = $request->input('from');
        $to   = $request->input('to');

        $query1 = DailyActivity::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query1->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivities = $query1->get();

        $query2 = DailyActivityFurther::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query2->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivityFurthers = $query2->get();

        $query3 = DailyActivitySlaughterHouse::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query3->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivitySlaughterHouses = $query3->get();

        $allDetails = [];

        foreach ($dailyActivities as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        foreach ($dailyActivityFurthers as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        foreach ($dailyActivitySlaughterHouses as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        usort($allDetails, function ($a, $b) {
            return strtotime($b['tanggal']) <=> strtotime($a['tanggal']);
        });

        return view('pages.admin_production.employee_productivity.detail', compact(
            'employee',
            'allDetails',
        ));
    }

    public function generalManagerDetail(Request $request, $employee_id)
    {
        $employee = Employee::findOrFail($employee_id);

        $from = $request->input('from');
        $to   = $request->input('to');

        $query1 = DailyActivity::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query1->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivities = $query1->get();

        $query2 = DailyActivityFurther::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query2->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivityFurthers = $query2->get();

        $query3 = DailyActivitySlaughterHouse::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query3->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivitySlaughterHouses = $query3->get();

        $allDetails = [];

        foreach ($dailyActivities as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        foreach ($dailyActivityFurthers as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        foreach ($dailyActivitySlaughterHouses as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        usort($allDetails, function ($a, $b) {
            return strtotime($b['tanggal']) <=> strtotime($a['tanggal']);
        });

        return view('pages.general_manager.employee-productivity.detail', compact(
            'employee',
            'allDetails',
        ));
    }

    public function managerDetail(Request $request, $employee_id)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $employee = Employee::where('department_id', $managerDepartmentId)
            ->findOrFail($employee_id);

        $from = $request->input('from');
        $to   = $request->input('to');

        $query1 = DailyActivity::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query1->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivities = $query1->get();

        $query2 = DailyActivityFurther::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query2->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivityFurthers = $query2->get();

        $query3 = DailyActivitySlaughterHouse::where('employee_id', $employee_id)->with('details.product');
        if ($from && $to) {
            $query3->whereBetween('tanggal', [$from, $to]);
        }
        $dailyActivitySlaughterHouses = $query3->get();

        $allDetails = [];

        foreach ($dailyActivities as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        foreach ($dailyActivityFurthers as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        foreach ($dailyActivitySlaughterHouses as $activity) {
            foreach ($activity->details as $detail) {
                $allDetails[] = [
                    'tanggal'     => $activity->tanggal,
                    'product'     => $detail->product->material_name ?? '-',
                    'total_kg'    => $detail->total_kg,
                    'total_harga' => $detail->total_harga,
                ];
            }
        }

        usort($allDetails, function ($a, $b) {
            return strtotime($b['tanggal']) <=> strtotime($a['tanggal']);
        });

        return view('pages.manager.employee-productivity.detail', compact(
            'employee',
            'allDetails',
        ));
    }
}