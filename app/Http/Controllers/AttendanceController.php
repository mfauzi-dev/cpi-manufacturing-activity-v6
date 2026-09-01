<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Outsourcing;
use App\Models\PsGroup;
use Illuminate\Http\Request;
use App\Exports\AttendanceSummaryExport;
use App\Models\PenggajianHarian;
use App\Models\WageConfig;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    public function groupsByDepartment(Request $request)
    {
        $departmentId = $request->department_id;

        if (!$departmentId) {
            return response()->json([]);
        }

        $groups = PsGroup::whereHas('costCenter', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($groups);
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

    public function index(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'employee_status' => ['nullable', 'string'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $date = $request->date ?? now()->toDateString();
        $status = $request->status;
        $employeeStatus = $request->employee_status;
        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $groupId = $request->ps_group_id;
        $search = $request->search;
        $size = $request->size ?? 50;

        $query = Employee::with([
            'costCenter',
            'psGroup',
            'outsourcing',
            'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date)
                ->with('inputBy');
            }
        ])->where('department_id', $user->department_id);

        if ($request->outsourcing_id) {
            $query->where('employees.outsourcing_id', $request->outsourcing_id);
        }

        if ($request->cost_center_id) {
            $query->where('employees.cost_center_id', $request->cost_center_id);
        }

        if ($request->ps_group_id) {
            $query->where('employees.ps_group_id', $request->ps_group_id);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                ->orWhere('employees.nik', 'like', "%{$search}%");
            });
        }

        if ($employeeStatus) {
            $query->where('employees.employee_status', $employeeStatus);
        }

        // Filter Status Attendance
        if ($status) {
            $query->whereHas('attendances', function ($q) use ($date, $status) {
                $q->whereDate('date', $date)
                ->where('status', $status);
            });
        }

        $employees = $query
            ->leftJoin('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->select('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($size)
            ->withQueryString();

        $outsourcings = Outsourcing::orderBy('name')->get();

        $costCenters = CostCenter::where('department_id', auth()->user()->department_id)->get();

        $groups = PsGroup::orderBy('name')->get();

        return view(
            'pages.admin_production.attendance.index',
            compact(
                'employees',
                'outsourcings',
                'costCenters',
                'date'
            )
        );
    }

    public function generalManagerIndex(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'employee_status' => ['nullable', 'string'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $date = $request->date ?? now()->toDateString();
        $employeeStatus = $request->employee_status;

        $query = Employee::with([
            'costCenter',
            'psGroup',
            'outsourcing',
            'department',
            'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date)->with('inputBy');
            }
        ]);

        if ($request->department_id) {
            $query->where('employees.department_id', $request->department_id);
        }

        if ($request->outsourcing_id) {
            $query->where('employees.outsourcing_id', $request->outsourcing_id);
        }

        if ($request->cost_center_id) {
            $query->where('employees.cost_center_id', $request->cost_center_id);
        }

        if ($request->ps_group_id) {
            $query->where('employees.ps_group_id', $request->ps_group_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('employees.name', 'like', "%{$request->search}%")
                ->orWhere('employees.nik', 'like', "%{$request->search}%");
            });
        }

        if ($employeeStatus) {
            $query->where('employees.employee_status', $employeeStatus);
        }

        if ($request->status) {
            $query->whereHas('attendances', function ($q) use ($date, $request) {
                $q->whereDate('date', $date)->where('status', $request->status);
            });
        }

        $employees = $query
            ->leftJoin('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->select('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($request->size ?? 50)
            ->withQueryString();

        return view('pages.general_manager.attendance.index', [
            'employees' => $employees,
            'outsourcings' => Outsourcing::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'date' => $date,
        ]);
    }

    public function managerIndex(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'employee_status' => ['nullable', 'string'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $date = $request->date ?? now()->toDateString();
        $employeeStatus = $request->employee_status; 

        $query = Employee::with([
            'costCenter',
            'psGroup',
            'outsourcing',
            'department',
            'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date)->with('inputBy');
            }
        ])->where('employees.department_id', $managerDepartmentId);

        if ($request->outsourcing_id) {
            $query->where('employees.outsourcing_id', $request->outsourcing_id);
        }

        if ($request->cost_center_id) {
            $query->where('employees.cost_center_id', $request->cost_center_id);
        }

        if ($request->ps_group_id) {
            $query->where('employees.ps_group_id', $request->ps_group_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('employees.name', 'like', "%{$request->search}%")
                ->orWhere('employees.nik', 'like', "%{$request->search}%");
            });
        }

        if ($employeeStatus) {
            $query->where('employees.employee_status', $employeeStatus);
        }

        if ($request->status) {
            $query->whereHas('attendances', function ($q) use ($date, $request) {
                $q->whereDate('date', $date)->where('status', $request->status);
            });
        }

        $employees = $query
            ->leftJoin('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->select('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($request->size ?? 50)
            ->withQueryString();

        return view('pages.manager.attendance.index', [
            'employees' => $employees,
            'outsourcings' => Outsourcing::orderBy('name')->get(),
            'managerDepartment' => Department::find($managerDepartmentId),
            'date' => $date,
        ]);
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
 
        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'employee_status' => ['nullable', 'string'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $employeeStatus = $request->employee_status;
        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $search = $request->search;
        $size = $request->size ?? 50;
 
        $query = Employee::with(['psGroup', 'outsourcing'])->where('department_id', $user->department_id);
        
        if ($request->outsourcing_id) {
            $query->where('employees.outsourcing_id', $request->outsourcing_id);
        }

        if ($request->cost_center_id) {
            $query->where('employees.cost_center_id', $request->cost_center_id);
        }

        if ($request->ps_group_id) {
            $query->where('employees.ps_group_id', $request->ps_group_id);
        }
 
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                    ->orWhere('employees.nik', 'like', "%{$search}%");
            });
        }

        if ($employeeStatus) {
            $query->where('employees.employee_status', $employeeStatus);
        }
 
        // Count attendance per status, untuk bulan terpilih
        $query->withCount([
            'attendances as total_hadir' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'hadir')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_izin' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'izin')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_sakit' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'sakit')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_cuti' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'cuti')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_alfa' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'alfa')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
        ]);

        $totalEmployee = (clone $query)->count();
 
        $employees = $query
            ->leftJoin('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->addSelect('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($size)
            ->withQueryString();
 
        $outsourcings = Outsourcing::orderBy('name')->get();
 
        $costCenters = CostCenter::where('department_id', auth()->user()->department_id)->get();
        return view(
            'pages.admin_production.attendance.summary',
            compact(
                'employees',
                'outsourcings',
                'costCenters',
                'monthNum',
                'year',
                'totalEmployee'
            )
        );
    }

    public function detail(Request $request, Employee $employee)
    {
         $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'outsourcing_id' => ['nullable', 'integer'],
            'group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
 
        $attendances = $employee->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date')
            ->get();
 
        // Semua filter dari summary, dibawa lagi buat tombol "Kembali ke Summary"
        $filters = $request->only(['month', 'year', 'outsourcing_id', 'group_id', 'search', 'size']);
 
        return view(
            'pages.admin_production.attendance.detail',
            compact(
                'employee',
                'attendances',
                'monthNum',
                'year',
                'filters'
            )
        );
    }

    public function generalManagerSummary(Request $request)
    {
        $user = auth()->user();
 
        abort_unless($user->role->can_access_all_departments, 403);
 
        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'department_id' => ['nullable', 'integer'],
            'employee_status' => ['nullable', 'string'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $departmentId = $request->department_id;
        $employeeStatus = $request->employee_status;
        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $search = $request->search;
        $size = $request->size ?? 50;
 
        $query = Employee::with(['department', 'costCenter', 'psGroup', 'outsourcing']);
 
        if ($request->department_id) {
            $query->where('employees.department_id', $request->department_id);
        }

        if ($request->outsourcing_id) {
            $query->where('employees.outsourcing_id', $request->outsourcing_id);
        }

        if ($request->cost_center_id) {
            $query->where('employees.cost_center_id', $request->cost_center_id);
        }

        if ($request->ps_group_id) {
            $query->where('employees.ps_group_id', $request->ps_group_id);
        }
 
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                    ->orWhere('employees.nik', 'like', "%{$search}%");
            });
        }

        if ($employeeStatus) {
            $query->where('employees.employee_status', $employeeStatus);
        }

        // Count attendance per status, untuk bulan terpilih
        $query->withCount([
            'attendances as total_hadir' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'hadir')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_izin' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'izin')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_sakit' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'sakit')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_cuti' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'cuti')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_alfa' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'alfa')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
        ]);

        $totalEmployee = (clone $query)->count();
 
        $employees = $query
            ->leftJoin('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->addSelect('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($size)
            ->withQueryString();
 
        $departments = Department::orderBy('name')->get();
 
        $outsourcings = Outsourcing::orderBy('name')->get();
 
        return view(
            'pages.general_manager.attendance.summary',
            compact(
                'employees',
                'departments',
                'outsourcings',
                'monthNum',
                'year',
                'totalEmployee'
            )
        );
    }

    public function managerSummary(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'employee_status' => ['nullable', 'string'], 
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $employeeStatus = $request->employee_status;
        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $search = $request->search;
        $size = $request->size ?? 50;

        $query = Employee::with(['department', 'costCenter', 'psGroup', 'outsourcing'])
            ->where('employees.department_id', $managerDepartmentId);

        if ($request->outsourcing_id) {
            $query->where('employees.outsourcing_id', $request->outsourcing_id);
        }

        if ($request->cost_center_id) {
            $query->where('employees.cost_center_id', $request->cost_center_id);
        }

        if ($request->ps_group_id) {
            $query->where('employees.ps_group_id', $request->ps_group_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                    ->orWhere('employees.nik', 'like', "%{$search}%");
            });
        }

        if ($employeeStatus) {
            $query->where('employees.employee_status', $employeeStatus);
        }

        $query->withCount([
            'attendances as total_hadir' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'hadir')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_izin' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'izin')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_sakit' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'sakit')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_cuti' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'cuti')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
            'attendances as total_alfa' => function ($q) use ($year, $monthNum) {
                $q->where('status', 'alfa')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum);
            },
        ]);

        $totalEmployee = (clone $query)->count();

        $employees = $query
            ->leftJoin('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->addSelect('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($size)
            ->withQueryString();

        $outsourcings = Outsourcing::orderBy('name')->get();
        $managerDepartment = Department::find($managerDepartmentId);

        return view(
            'pages.manager.attendance.summary',
            compact(
                'employees',
                'managerDepartment',
                'outsourcings',
                'monthNum',
                'year',
                'totalEmployee'
            )
        );
    }

    public function generalManagerDetail(Request $request, Employee $employee)
    {
        $user = auth()->user();
 
        abort_unless($user->role->can_access_all_departments, 403);
 
        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'department_id' => ['nullable', 'integer'],
            'outsourcing_id' => ['nullable', 'integer'],
            'group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
 
        $attendances = $employee->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date')
            ->get();
 
        // Semua filter dari summaryAllDepartment, dibawa lagi buat tombol "Kembali ke Summary"
        $filters = $request->only([
            'month', 'year', 'department_id', 'outsourcing_id', 'group_id', 'search', 'size',
        ]);
 
        return view(
            'pages.general_manager.attendance.detail',
            compact(
                'employee',
                'attendances',
                'monthNum',
                'year',
                'filters'
            )
        );
    }

    public function managerDetail(Request $request, Employee $employee)
    {
        $managerDepartmentId = auth()->user()->department_id;

        if (!$managerDepartmentId) {
            abort(403, 'Akun Anda belum terhubung ke department manapun.');
        }

        if ($employee->department_id !== $managerDepartmentId) {
            abort(404);
        }
 
        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'department_id' => ['nullable', 'integer'],
            'outsourcing_id' => ['nullable', 'integer'],
            'group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
 
        $attendances = $employee->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date')
            ->get();
 
        // Semua filter dari summaryAllDepartment, dibawa lagi buat tombol "Kembali ke Summary"
        $filters = $request->only([
            'month', 'year', 'department_id', 'outsourcing_id', 'group_id', 'search', 'size',
        ]);
 
        return view(
            'pages.manager.attendance.detail',
            compact(
                'employee',
                'attendances',
                'monthNum',
                'year',
                'filters'
            )
        );
    }

    public function generalManagerCreate(Request $request)
    {
        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $date = $request->date ?? now()->toDateString();
        $employeeStatus = $request->employee_status;
        $search = $request->search;

        $departments = Department::orderBy('name')->get();

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup'
        ]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($psGroupId) {
            $query->where('ps_group_id', $psGroupId);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $employees = $query
            ->with([
                'attendances' => function ($q) use ($date) {
                    $q->whereDate('date', $date);
                }
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.general_manager.attendance.create', compact([
            'employees',
            'departments',
            'employeeStatus',
            'departmentId',
            'costCenterId',
            'psGroupId',
            'date'
        ]));
    }

    public function managerCreate(Request $request)
    {
        $user = auth()->user();

        $departmentId = $user->department_id;

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $employeeStatus = $request->employee_status;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $search = $request->search;
        $date = $request->date ?? now()->toDateString();

        $query = Employee::with([
            'outsourcing',
            'psGroup',
            'costCenter'
        ])->where('department_id', $departmentId);

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($psGroupId) {
            $query->where('ps_group_id', $psGroupId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $employees = $query
            ->with([
                'attendances' => function ($q) use ($date) {
                    $q->whereDate('date', $date);
                }
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.manager.attendance.create',
            compact(
                'employees',
                'costCenters',
                'date',
                'costCenterId',
                'psGroupId'
            )
        );
    }
    
    public function create(Request $request)
    {
        $user = auth()->user();

        $departmentId = $user->department_id;

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $employeeStatus = $request->employee_status;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $search = $request->search;
        $date = $request->date ?? now()->toDateString();

        $query = Employee::with([
            'outsourcing',
            'psGroup',
            'costCenter'
        ])->where('department_id', $departmentId);

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($psGroupId) {
            $query->where('ps_group_id', $psGroupId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $employees = $query
            ->with([
                'attendances' => function ($q) use ($date) {
                    $q->whereDate('date', $date);
                }
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.admin_production.attendance.create',
            compact(
                'employees',
                'costCenters',
                'date',
                'costCenterId',
                'psGroupId'
            )
        );
    }

    public function bulkStore(Request $request)
    {        
        $request->validate([
            'date' => ['required', 'date'],
            'employees' => ['required', 'array'],
        ]);

        $date = Carbon::parse($request->date);
        $month = $date->month;
        $year = $date->year;

        $config = WageConfig::where('tahun', $year)->first();

        foreach ($request->employees as $employeeId => $data) {

            $status = $data['status'] ?? null;

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $request->date,
                ],
                [
                    'status' => $status,
                    'keterangan_izin' => $data['keterangan_izin'] ?? null,
                    'input_by' => auth()->id(),
                ]
            );

            $employee = Employee::find($employeeId);

            if ($employee && $employee->employee_status === 'harian') {

                $payroll = PenggajianHarian::where('employee_id', $employeeId)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if ($payroll && $payroll->ump_used > 0) {
                    // Gunakan snapshot yang sudah tersimpan
                    $ump = $payroll->ump_used;
                    $hariKerjaStandar = $payroll->hari_kerja_standar_used;
                } else {
                    // Payroll belum ada, ambil config sebagai snapshot awal
                    $ump = $config->ump ?? 0;
                    $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
                }

                $workDays = Attendance::where('employee_id', $employeeId)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('status', 'hadir')
                    ->count();

                $upahHarian = $hariKerjaStandar > 0
                    ? round(($ump / $hariKerjaStandar) * $workDays, 2)
                    : 0;

                $jamsostek = round(
                    $upahHarian * 0.0489,
                    2
                );

                $bpjsKesehatan = round(
                    $upahHarian * 0.04,
                    2
                );

                $bpjsPensiun = round(
                    $upahHarian * 0.02,
                    2
                );

                $managemenFeePercent = 175000 / 25;

                $managemenFee = $workDays * $managemenFeePercent;

                $grandTotalUpah = $upahHarian + $jamsostek + $bpjsKesehatan + $bpjsPensiun + $managemenFee;

                PenggajianHarian::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'period_month' => $month,
                        'period_year' => $year,
                    ],
                    [
                        'work_days' => $workDays,
                        'ump_used' => $ump,
                        'hari_kerja_standar_used' => $hariKerjaStandar,
                        'upah_harian' => $upahHarian,
                        'jamsostek' => $jamsostek,
                        'bpjs_kesehatan' => $bpjsKesehatan,
                        'bpjs_pensiun' => $bpjsPensiun,
                        'managemen_fee' => $managemenFee,
                        'grand_total_upah' => $grandTotalUpah,
                        'net_salary' => $grandTotalUpah,
                    ]
                );
            }
        }

        return redirect()->route('admin-production.attendance.index')->with('success', 'Absensi berhasil disimpan');
    }

    public function generalManagerBulkStore(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'employees' => ['required', 'array'],
        ]);

        $date = Carbon::parse($request->date);
        $month = $date->month;
        $year = $date->year;

        $config = WageConfig::where('tahun', $year)->first();

        foreach ($request->employees as $employeeId => $data) {

            $status = $data['status'] ?? null;

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $request->date,
                ],
                [
                    'status' => $status,
                    'keterangan_izin' => $data['keterangan_izin'] ?? null,
                    'input_by' => auth()->id(),
                ]
            );

            $employee = Employee::find($employeeId);

            if ($employee && $employee->employee_status === 'harian') {

                $payroll = PenggajianHarian::where('employee_id', $employeeId)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if ($payroll && $payroll->ump_used > 0) {
                    // Gunakan snapshot yang sudah tersimpan
                    $ump = $payroll->ump_used;
                    $hariKerjaStandar = $payroll->hari_kerja_standar_used;
                } else {
                    // Payroll belum ada, ambil config sebagai snapshot awal
                    $ump = $config->ump ?? 0;
                    $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
                }

                $workDays = Attendance::where('employee_id', $employeeId)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('status', 'hadir')
                    ->count();

                $upahHarian = $hariKerjaStandar > 0
                    ? round(($ump / $hariKerjaStandar) * $workDays, 2)
                    : 0;

                $jamsostek = round(
                    $upahHarian * 0.0489,
                    2
                );

                $bpjsKesehatan = round(
                    $upahHarian * 0.04,
                    2
                );

                $bpjsPensiun = round(
                    $upahHarian * 0.02,
                    2
                );

                $managemenFeePercent = 175000 / 25;

                $managemenFee = $workDays * $managemenFeePercent;

                $grandTotalUpah = $upahHarian + $jamsostek + $bpjsKesehatan + $bpjsPensiun + $managemenFee;

                PenggajianHarian::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'period_month' => $month,
                        'period_year' => $year,
                    ],
                    [
                        'work_days' => $workDays,
                        'ump_used' => $ump,
                        'hari_kerja_standar_used' => $hariKerjaStandar,
                        'upah_harian' => $upahHarian,
                        'jamsostek' => $jamsostek,
                        'bpjs_kesehatan' => $bpjsKesehatan,
                        'bpjs_pensiun' => $bpjsPensiun,
                        'managemen_fee' => $managemenFee,
                        'grand_total_upah' => $grandTotalUpah,
                        'net_salary' => $grandTotalUpah,
                    ]
                );
            }
        }

        return redirect()->route('general-manager.attendance.index')->with('success', 'Absensi berhasil disimpan');
    }

    public function managerBulkStore(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'employees' => ['required', 'array'],
        ]);

        $date = Carbon::parse($request->date);
        $month = $date->month;
        $year = $date->year;

        $config = WageConfig::where('tahun', $year)->first();

        foreach ($request->employees as $employeeId => $data) {

            $status = $data['status'] ?? null;

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $request->date,
                ],
                [
                    'status' => $status,
                    'keterangan_izin' => $data['keterangan_izin'] ?? null,
                    'input_by' => auth()->id(),
                ]
            );

            $employee = Employee::find($employeeId);

            if ($employee && $employee->employee_status === 'harian') {

                $payroll = PenggajianHarian::where('employee_id', $employeeId)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if ($payroll && $payroll->ump_used > 0) {
                    // Gunakan snapshot yang sudah tersimpan
                    $ump = $payroll->ump_used;
                    $hariKerjaStandar = $payroll->hari_kerja_standar_used;
                } else {
                    // Payroll belum ada, ambil config sebagai snapshot awal
                    $ump = $config->ump ?? 0;
                    $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
                }

                $workDays = Attendance::where('employee_id', $employeeId)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('status', 'hadir')
                    ->count();

                $upahHarian = $hariKerjaStandar > 0
                    ? round(($ump / $hariKerjaStandar) * $workDays, 2)
                    : 0;

                
                $jamsostek = round(
                    $upahHarian * 0.0489,
                    2
                );

                $bpjsKesehatan = round(
                    $upahHarian * 0.04,
                    2
                );

                $bpjsPensiun = round(
                    $upahHarian * 0.02,
                    2
                );

                $managemenFeePercent = 175000 / 25;

                $managemenFee = $workDays * $managemenFeePercent;

                $grandTotalUpah = $upahHarian + $jamsostek + $bpjsKesehatan + $bpjsPensiun + $managemenFee;
                PenggajianHarian::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'period_month' => $month,
                        'period_year' => $year,
                    ],
                    [
                        'work_days' => $workDays,
                        'ump_used' => $ump,
                        'hari_kerja_standar_used' => $hariKerjaStandar,
                        'upah_harian' => $upahHarian,
                        'jamsostek' => $jamsostek,
                        'bpjs_kesehatan' => $bpjsKesehatan,
                        'bpjs_pensiun' => $bpjsPensiun,
                        'managemen_fee' => $managemenFee,
                        'grand_total_upah' => $grandTotalUpah,
                        'net_salary' => $grandTotalUpah,
                    ]
                );
            }
        }

        return redirect()->route('manager.attendance.index')->with('success', 'Absensi berhasil disimpan');
    }

    public function exportSummaryExcelGeneralManager(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $employeeStatus = $request->employee_status;
        $search = $request->search;

        $fileName = 'attendance-summary-' .
            $year . '-' .
            str_pad($month, 2, '0', STR_PAD_LEFT) .
            '.xlsx';

        return Excel::download(
            new AttendanceSummaryExport(
                $month,
                $year,
                $outsourcingId,
                $costCenterId,
                $psGroupId,
                $search,
                null,
                $employeeStatus
            ),
            $fileName
        );
    }

    public function exportSummaryExcelManager(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $employeeStatus = $request->employee_status;
        $search = $request->search;

        $fileName = 'attendance-summary-' .
            $year . '-' .
            str_pad($month, 2, '0', STR_PAD_LEFT) .
            '.xlsx';

        return Excel::download(
            new AttendanceSummaryExport(
                $month,
                $year,
                $outsourcingId,
                $costCenterId,
                $psGroupId,
                $search,
                $managerDepartmentId,
                $employeeStatus
            ),
            $fileName
        );
    }

    public function exportSummaryExcel(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $employeeStatus = $request->employee_status;
        $search = $request->search;

        $fileName = 'attendance-summary-' .
            $year . '-' .
            str_pad($month, 2, '0', STR_PAD_LEFT) .
            '.xlsx';

        return Excel::download(
            new AttendanceSummaryExport(
                $month,
                $year,
                $outsourcingId,
                $costCenterId,
                $psGroupId,
                $search,
                $departmentId,
                $employeeStatus
            ),
            $fileName
        );
    }

    public function exportSummaryPdf(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $employeeStatus = $request->employee_status;
        $search = $request->search;

        $startDate = Carbon::create(
            $year,
            $month,
            1
        )->startOfMonth();

        $endDate = Carbon::create(
            $year,
            $month,
            1
        )->endOfMonth();

        $query = Employee::query()
            ->where('department_id', $managerDepartmentId)
            ->with([
                'department',
                'outsourcing',
                'psGroup',
            ])

            ->withCount([
                'attendances as total_hadir' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'hadir');
                },

                'attendances as total_izin' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'izin');
                },

                'attendances as total_sakit' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'sakit');
                },

                'attendances as total_cuti' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'cuti');
                },

                'attendances as total_alfa' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'alfa');
                },
            ]);

        if ($outsourcingId) {
            $query->where('outsourcing_id', $outsourcingId);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($psGroupId) {
            $query->where('ps_group_id', $psGroupId);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $employees = $query
            ->orderBy('name')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Nama filter
        $outsourcingName = 'Semua OS';

        if ($outsourcingId) {
            $outsourcingName = Outsourcing::find($outsourcingId)?->name ?? '-';
        }

        $costCenterName = 'Semua Cost Center';

        if ($costCenterId) {
            $costCenterName = CostCenter::find($costCenterId)?->name ?? '-';
        }

        $psGroupName = 'Semua Group';

        if ($psGroupId) {
            $psGroupName = PsGroup::find($psGroupId)?->name ?? '-';
        }

        $employeeStatusLabels = [
            'cpi' => 'CPI',
            'borongan' => 'Borongan',
            'harian' => 'Harian',
        ];

        $employeeStatusName = $employeeStatus
            ? ($employeeStatusLabels[$employeeStatus] ?? $employeeStatus)
            : 'Semua Status Karyawan';
        

        $pdf = Pdf::loadView(
            'pages.admin_production.attendance.summary-pdf',
            [
                'employees' => $employees,
                'month' => $monthNames[$month],
                'year' => $year,
                'outsourcingName' => $outsourcingName,
                'costCenterName' => $costCenterName,
                'psGroupName' => $psGroupName,
                'employeeStatusName' => $employeeStatusName,
                'search' => $search,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'attendance-summary-' .
            $year . '-' .
            str_pad($month, 2, '0', STR_PAD_LEFT) .
            '.pdf'
        );
    }

    public function exportSummaryPdfGeneralManager(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $employeeStatus = $request->employee_status; 
        $search = $request->search;

        $startDate = Carbon::create(
            $year,
            $month,
            1
        )->startOfMonth();

        $endDate = Carbon::create(
            $year,
            $month,
            1
        )->endOfMonth();

        $query = Employee::query()
            ->with([
                'department',
                'outsourcing',
                'psGroup',
            ])

            ->withCount([
                'attendances as total_hadir' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'hadir');
                },

                'attendances as total_izin' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'izin');
                },

                'attendances as total_sakit' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'sakit');
                },

                'attendances as total_cuti' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'cuti');
                },

                'attendances as total_alfa' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'alfa');
                },
            ]);

        if ($outsourcingId) {
            $query->where('outsourcing_id', $outsourcingId);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($psGroupId) {
            $query->where('ps_group_id', $psGroupId);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $employees = $query
            ->orderBy('name')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Nama filter
        $outsourcingName = 'Semua OS';

        if ($outsourcingId) {
            $outsourcingName = Outsourcing::find($outsourcingId)?->name ?? '-';
        }

        $costCenterName = 'Semua Cost Center';

        if ($costCenterId) {
            $costCenterName = CostCenter::find($costCenterId)?->name ?? '-';
        }

        $psGroupName = 'Semua Group';

        if ($psGroupId) {
            $psGroupName = PsGroup::find($psGroupId)?->name ?? '-';
        }

        $employeeStatusLabels = [
            'cpi' => 'CPI',
            'borongan' => 'Borongan',
            'harian' => 'Harian',
        ];

        $employeeStatusName = $employeeStatus
            ? ($employeeStatusLabels[$employeeStatus] ?? $employeeStatus)
            : 'Semua Status Karyawan';

        $pdf = Pdf::loadView(
            'pages.general_manager.attendance.summary-pdf',
            [
                'employees' => $employees,
                'month' => $monthNames[$month],
                'year' => $year,
                'outsourcingName' => $outsourcingName,
                'costCenterName' => $costCenterName,
                'psGroupName' => $psGroupName,
                'employeeStatusName' => $employeeStatusName,
                'search' => $search,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'attendance-summary-' .
            $year . '-' .
            str_pad($month, 2, '0', STR_PAD_LEFT) .
            '.pdf'
        );
    }

    public function exportSummaryPdfManager(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;
        abort_unless($managerDepartmentId, 403, 'Akun Anda belum terhubung ke department manapun.');

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $employeeStatus = $request->employee_status; 
        $search = $request->search;

        $startDate = Carbon::create(
            $year,
            $month,
            1
        )->startOfMonth();

        $endDate = Carbon::create(
            $year,
            $month,
            1
        )->endOfMonth();

        $query = Employee::query()
            ->where('department_id', $managerDepartmentId)
            ->with([
                'department',
                'outsourcing',
                'psGroup',
            ])

            ->withCount([
                'attendances as total_hadir' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'hadir');
                },

                'attendances as total_izin' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'izin');
                },

                'attendances as total_sakit' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'sakit');
                },

                'attendances as total_cuti' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'cuti');
                },

                'attendances as total_alfa' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->where('status', 'alfa');
                },
            ]);

        if ($outsourcingId) {
            $query->where('outsourcing_id', $outsourcingId);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($psGroupId) {
            $query->where('ps_group_id', $psGroupId);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $employees = $query
            ->orderBy('name')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Nama filter
        $outsourcingName = 'Semua OS';

        if ($outsourcingId) {
            $outsourcingName = Outsourcing::find($outsourcingId)?->name ?? '-';
        }

        $costCenterName = 'Semua Cost Center';

        if ($costCenterId) {
            $costCenterName = CostCenter::find($costCenterId)?->name ?? '-';
        }

        $psGroupName = 'Semua Group';

        if ($psGroupId) {
            $psGroupName = PsGroup::find($psGroupId)?->name ?? '-';
        }

        $employeeStatusLabels = [
            'cpi' => 'CPI',
            'borongan' => 'Borongan',
            'harian' => 'Harian',
        ];

        $employeeStatusName = $employeeStatus
            ? ($employeeStatusLabels[$employeeStatus] ?? $employeeStatus)
            : 'Semua Status Karyawan';

        $pdf = Pdf::loadView(
            'pages.manager.attendance.summary-pdf',
            [
                'employees' => $employees,
                'month' => $monthNames[$month],
                'year' => $year,
                'outsourcingName' => $outsourcingName,
                'costCenterName' => $costCenterName,
                'psGroupName' => $psGroupName,
                'employeeStatusName' => $employeeStatusName,
                'search' => $search,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'attendance-summary-' .
            $year . '-' .
            str_pad($month, 2, '0', STR_PAD_LEFT) .
            '.pdf'
        );
    }
}
