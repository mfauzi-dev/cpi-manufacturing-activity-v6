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
use Maatwebsite\Excel\Facades\Excel;

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
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $date = $request->date ?? now()->toDateString();
        $status = $request->status;
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
        ]);

        // User biasa hanya department sendiri
        if (!$user->role->can_access_all_departments) {
            $query->where(
                'department_id',
                $user->department_id
            );
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

        // Filter Status Attendance
        if ($status) {
            $query->whereHas('attendances', function ($q) use ($date, $status) {
                $q->whereDate('date', $date)
                ->where('status', $status);
            });
        }

        $employees = $query
            ->join('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
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
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $date = $request->date ?? now()->toDateString();

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

        if ($request->status) {
            $query->whereHas('attendances', function ($q) use ($date, $request) {
                $q->whereDate('date', $date)->where('status', $request->status);
            });
        }

        $employees = $query
            ->join('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
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
        $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $date = $request->date ?? now()->toDateString();

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

        if ($request->status) {
            $query->whereHas('attendances', function ($q) use ($date, $request) {
                $q->whereDate('date', $date)->where('status', $request->status);
            });
        }

        $employees = $query
            ->join('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->select('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($request->size ?? 50)
            ->withQueryString();

        return view('pages.manager.attendance.index', [
            'employees' => $employees,
            'outsourcings' => Outsourcing::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'date' => $date,
        ]);
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
 
        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $outsourcingId = $request->outsourcing_id;
        $costCenterId = $request->cost_center_id;
        $psGroupId = $request->ps_group_id;
        $search = $request->search;
        $size = $request->size ?? 50;
 
        $query = Employee::with(['psGroup', 'outsourcing']);
 
        // User biasa hanya department sendiri
        if (!$user->role->can_access_all_departments) {
            $query->where(
                'department_id',
                $user->department_id
            );
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
 
        $employees = $query
            ->join('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
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
                'year'
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
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $departmentId = $request->department_id;
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
 
        $employees = $query
            ->join('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
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
                'year'
            )
        );
    }

    public function managerSummary(Request $request)
    {
        $user = auth()->user();
 
        abort_unless($user->role->can_access_all_departments, 403);
 
        $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'digits:4'],
            'department_id' => ['nullable', 'integer'],
            'outsourcing_id' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'integer'],
            'ps_group_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);
 
        $monthNum = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $departmentId = $request->department_id;
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
 
        $employees = $query
            ->join('ps_groups', 'employees.ps_group_id', '=', 'ps_groups.id')
            ->addSelect('employees.*')
            ->orderBy('ps_groups.name')
            ->orderBy('employees.name')
            ->paginate($size)
            ->withQueryString();
 
        $departments = Department::orderBy('name')->get();
 
        $outsourcings = Outsourcing::orderBy('name')->get();
 
        $groups = PsGroup::orderBy('name')->get();
 
        return view(
            'pages.manager.attendance.summary',
            compact(
                'employees',
                'departments',
                'outsourcings',
                'groups',
                'monthNum',
                'year'
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
            ->get();

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
            ->get();

        return view('pages.manager.attendance.create', compact([
            'employees',
            'departments',
            'departmentId',
            'costCenterId',
            'psGroupId',
            'date'
        ]));
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
        ]);

        if (!$user->role->can_access_all_departments) {
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
            ->get();

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
        }

        return redirect()
            ->route('admin-production.attendance.index')
            ->with(
                'success',
                'Absensi berhasil disimpan'
            );
    }

    public function generalManagerBulkStore(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'employees' => ['required', 'array'],
        ]);

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
        }

        return redirect()
            ->route('general-manager.attendance.index')
            ->with(
                'success',
                'Absensi berhasil disimpan'
            );
    }

    public function managerBulkStore(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'employees' => ['required', 'array'],
        ]);

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
        }

        return redirect()
            ->route('manager.attendance.index')
            ->with(
                'success',
                'Absensi berhasil disimpan'
            );
    }
}
