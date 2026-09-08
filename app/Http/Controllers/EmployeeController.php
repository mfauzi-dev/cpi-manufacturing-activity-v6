<?php

namespace App\Http\Controllers;

use App\Imports\OutsourcingEmployeeImport;
use App\Imports\PermanentEmployeeImport;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Level;
use App\Models\Outsourcing;
use App\Models\Position;
use App\Models\PsGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function getCostCenterByDepartment($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($costCenters);
    }

    public function getPsGroups($costCenterId)
    {
        $psGroups = PsGroup::where('cost_center_id', $costCenterId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($psGroups);
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'employment_status' => ['nullable', 'in:permanent,outsourcing'],
            'employee_status' => ['nullable', 'in:cpi,borongan,harian'],
            'is_active' => ['nullable', 'in:0,1'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 50);
        $employmentStatus = $request->employment_status;
        $employeeStatus = $request->employee_status;
        $isActive = $request->is_active;
        $costCenterId = $request->cost_center_id;
        $positionId = $request->position_id;
        $levelId = $request->level_id;

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup',
            'position',
            'level'
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($employmentStatus) {
            $query->where('employment_status', $employmentStatus);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $query
            ->orderBy(
                CostCenter::select('name')
                    ->whereColumn('cost_centers.id', 'employees.cost_center_id')
            )
            ->orderBy(
                PsGroup::select('name')
                    ->whereColumn('ps_groups.id', 'employees.ps_group_id')
            )
            ->orderBy(
                Outsourcing::select('name')
                    ->whereColumn('outsourcings.id', 'employees.outsourcing_id')
            )
            ->orderBy('employees.name');

        $employees = $query
            ->paginate($size)
            ->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();
        $positionList = Position::orderBy('name')->get();
        $levelList = Level::orderBy('name')->get();

        return view(
            'pages.admin.employee.index',
            compact(
                'employees',
                'search',
                'employmentStatus',
                'employeeStatus',
                'isActive',
                'costCenterId',
                'positionId',
                'levelId',
                'costCenterList',
                'positionList',
                'levelList'
            )
        );
    }

    public function generalManagerIndex(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'employment_status' => ['nullable', 'in:permanent,outsourcing'],
            'employee_status' => ['nullable', 'in:cpi,borongan,harian'],
            'is_active' => ['nullable', 'in:0,1'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 50);
        $employmentStatus = $request->employment_status;
        $employeeStatus = $request->employee_status;
        $isActive = $request->is_active;
        $costCenterId = $request->cost_center_id;
        $positionId = $request->position_id;
        $levelId = $request->level_id;

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup',
            'position',
            'level',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($employmentStatus) {
            $query->where('employment_status', $employmentStatus);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $query
            ->orderBy(
                CostCenter::select('name')
                    ->whereColumn('cost_centers.id', 'employees.cost_center_id')
            )
            ->orderBy(
                PsGroup::select('name')
                    ->whereColumn('ps_groups.id', 'employees.ps_group_id')
            )
            ->orderBy(
                Outsourcing::select('name')
                    ->whereColumn('outsourcings.id', 'employees.outsourcing_id')
            )
            ->orderBy('employees.name');

        $employees = $query
            ->paginate($size)
            ->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();
        $positionList = Position::orderBy('name')->get();
        $levelList = Level::orderBy('name')->get();

        return view(
            'pages.general_manager.employee.index',
            compact(
                'employees',
                'search',
                'employmentStatus',
                'employeeStatus',
                'isActive',
                'costCenterId',
                'positionId',
                'levelId',
                'costCenterList',
                'positionList',
                'levelList'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');
        $isHrDepartment = $departmentName === 'personalia dan general affair';

        $managerDepartmentId = $user->department_id;

        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'employment_status' => ['nullable', 'in:permanent,outsourcing'],
            'employee_status' => ['nullable', 'in:cpi,borongan,harian'],
            'is_active' => ['nullable', 'in:0,1'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 50);
        $employmentStatus = $request->employment_status;
        $employeeStatus = $request->employee_status;
        $isActive = $request->is_active;
        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;
        $positionId = $request->position_id;
        $levelId = $request->level_id;

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup',
            'position',
            'level'
        ]);

        if ($isHrDepartment) {
            // HR bisa lihat semua department, atau filter ke department tertentu
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
        } else {
            // Non-HR selalu dibatasi ke department-nya sendiri
            $query->where('department_id', $managerDepartmentId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($employmentStatus) {
            $query->where('employment_status', $employmentStatus);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        $query
            ->orderBy(
                CostCenter::select('name')
                    ->whereColumn('cost_centers.id', 'employees.cost_center_id')
            )
            ->orderBy(
                PsGroup::select('name')
                    ->whereColumn('ps_groups.id', 'employees.ps_group_id')
            )
            ->orderBy(
                Outsourcing::select('name')
                    ->whereColumn('outsourcings.id', 'employees.outsourcing_id')
            )
            ->orderBy('employees.name');

        $employees = $query
            ->paginate($size)
            ->withQueryString();

        // Dropdown department: cuma HR yang butuh, non-HR gak perlu pilih (udah fix ke departmentnya)
        $departmentList = $isHrDepartment
            ? Department::orderBy('name')->get()
            : collect();

        // Dropdown cost center: awal load berdasarkan department yang lagi difilter (atau semua kalau HR belum filter)
        if ($isHrDepartment) {
            $costCenterList = $departmentId
                ? CostCenter::where('department_id', $departmentId)->orderBy('name')->get()
                : CostCenter::orderBy('name')->get();
        } else {
            $costCenterList = CostCenter::where('department_id', $managerDepartmentId)->orderBy('name')->get();
        }

        $positionList = Position::orderBy('name')->get();
        $levelList = Level::orderBy('name')->get();

        return view(
            'pages.manager.employee.index',
            compact(
                'employees',
                'search',
                'employmentStatus',
                'employeeStatus',
                'isActive',
                'departmentId',
                'costCenterId',
                'positionId',
                'levelId',
                'departmentList',
                'costCenterList',
                'positionList',
                'levelList',
                'isHrDepartment'
            )
        );
    }

    public function create()
    {
        $outsourcingList = Outsourcing::orderBy('name')->get();

        $costCenters = CostCenter::orderBy('name')->get();

        $psGroups = PsGroup::with('costCenter')
            ->orderBy('name')
            ->get();

        $positions = Position::orderBy('name')->get();

        $levels = Level::orderBy('name')->get();

        $departments = Department::orderBy('name')->get();

        return view(
            'pages.admin.employee.create',
            compact(
                'outsourcingList',
                'costCenters',
                'psGroups',
                'positions',
                'levels',
                'departments'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => [
                'nullable',
                'string',
                'max:50',
                'unique:employees,nik',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'employment_status' => [
                'required',
                'in:permanent,outsourcing',
            ],

            'employee_status' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'in:borongan,harian,harian_kontrak',
            ],

            'outsourcing_id' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'exists:outsourcings,id',
            ],

            'department_id' => [
                'nullable',
                'exists:departments,id',
            ],

            'cost_center_id' => [
                'nullable',
                'exists:cost_centers,id',
            ],

            'ps_group_id' => [
                'nullable',
                'exists:ps_groups,id',
            ],

            'position_id' => [
                'nullable',
                'exists:positions,id',
            ],

            'level_id' => [
                'nullable',
                'exists:levels,id',
            ],

            'personel_area' => [
                'nullable',
                'string',
            ],

            'gender' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $employeeStatus = $request->employment_status === 'permanent'
            ? 'cpi'
            : $request->employee_status;

        $outsourcingId = $request->employment_status === 'outsourcing'
            ? $request->outsourcing_id
            : null;

        Employee::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'employment_status' => $request->employment_status,
            'employee_status' => $employeeStatus,
            'outsourcing_id' => $outsourcingId,
            'department_id' => $request->department_id,
            'cost_center_id' => $request->cost_center_id,
            'ps_group_id' => $request->ps_group_id,
            'position_id' => $request->position_id,
            'level_id' => $request->level_id,
            'personel_area' => $request->personel_area,
            'gender' => $request->gender,
            'is_active' => $request->is_active,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function importPage()
    {
        $outsourcings = Outsourcing::orderBy('name')->get();

        return view(
            'pages.admin.employee.import',
            compact('outsourcings')
        );
    }

    public function upload(Request $request)
    {
        $request->validate([
            'employment_status' => [
                'required',
                'in:permanent,outsourcing',
            ],

            'outsourcing_id' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'exists:outsourcings,id',
            ],

            'employee_status' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'in:borongan,harian',
            ],

            'file' => [
                'required',
                'mimes:xlsx,xls',
            ],
        ]);

        try {
            if ($request->employment_status === 'permanent') {
                Excel::import(
                    new PermanentEmployeeImport(
                        $request->employment_status
                    ),
                    $request->file('file')
                );
            } else {
                $outsourcing = Outsourcing::findOrFail(
                    $request->outsourcing_id
                );

                Excel::import(
                    new OutsourcingEmployeeImport(
                        $outsourcing,
                        $request->employee_status
                    ),
                    $request->file('file')
                );
            }

            return redirect()
                ->route('admin.employee.index')
                ->with('success', 'Import karyawan berhasil.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        $employee = Employee::with([
            'outsourcing',
            'department',
            'costCenter',
            'psGroup',
            'position',
        ])->findOrFail($id);

        return view(
            'pages.admin.employee.detail',
            compact('employee')
        );
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        $outsourcingList = Outsourcing::orderBy('name')->get();

        $departmentList = Department::orderBy('name')->get();

        $costCenterList = CostCenter::orderBy('name')->get();

        $psGroupList = PsGroup::orderBy('name')->get();

        $positionList = Position::orderBy('name')->get();

        $levels = Level::orderBy('name')->get();

        return view(
            'pages.admin.employee.edit',
            compact(
                'employee',
                'outsourcingList',
                'departmentList',
                'costCenterList',
                'psGroupList',
                'positionList',
                'levels'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'nik' => [
                'nullable',
                Rule::unique('employees', 'nik')->ignore($id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'employment_status' => [
                'required',
                'in:permanent,outsourcing',
            ],

            'outsourcing_id' => [
                'nullable',
                'required_if:employment_status,outsourcing',
                'exists:outsourcings,id',
            ],

            'employee_status' => [
                'nullable',
                'required_if:employment_status,outsourcing',
                'in:cpi,borongan,harian,harian_kontrak',
            ],

            'department_id' => [
                'nullable',
                'exists:departments,id',
            ],

            'cost_center_id' => [
                'nullable',
                'exists:cost_centers,id',
            ],

            'ps_group_id' => [
                'nullable',
                'exists:ps_groups,id',
            ],

            'position_id' => [
                'nullable',
                'exists:positions,id',
            ],

            'level_id' => [
                'nullable',
                'exists:levels,id',
            ],

            'personel_area' => [
                'nullable',
                'string',
            ],

            'gender' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $employee->update([
            'nik' => $request->nik,
            'name' => $request->name,
            'employment_status' => $request->employment_status,

            'outsourcing_id' => $request->employment_status === 'outsourcing'
                ? $request->outsourcing_id
                : null,

            'employee_status' => $request->employment_status === 'outsourcing'
                ? $request->employee_status
                : 'cpi',

            'department_id' => $request->department_id,
            'cost_center_id' => $request->cost_center_id,
            'ps_group_id' => $request->ps_group_id,
            'position_id' => $request->position_id,
            'level_id' => $request->level_id,
            'personel_area' => $request->personel_area,
            'gender' => $request->gender,
            'is_active' => $request->is_active,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $employee = Employee::findOrFail($id);

        $employee->update([
            'is_active' => $request->is_active,
        ]);

        return back()->with(
            'success',
            'Status karyawan berhasil diperbarui.'
        );
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->delete();

        return redirect()
            ->route('admin.employee.index')
            ->with(
                'success',
                'Data karyawan berhasil dihapus.'
            );
    }

    public function managerCreate()
    {
        $managerDepartmentId = auth()->user()->department_id;

        $outsourcingList = Outsourcing::orderBy('name')->get();

        $departmentList = Department::where('id', $managerDepartmentId)
            ->orderBy('name')
            ->get();

        $costCenterList = CostCenter::where('department_id', $managerDepartmentId)
            ->orderBy('name')
            ->get();

        $psGroupList = PsGroup::whereHas('costCenter', function ($query) use ($managerDepartmentId) {
            $query->where('department_id', $managerDepartmentId);
        })
            ->orderBy('name')
            ->get();

        $positionList = Position::orderBy('name')->get();

        $levels = Level::orderBy('name')->get();

        return view(
            'pages.manager.employee.create',
            compact(
                'outsourcingList',
                'departmentList',
                'costCenterList',
                'psGroupList',
                'positionList',
                'levels'
            )
        );
    }

    public function managerEdit($id)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');
        $isHrDepartment = $departmentName === 'personalia dan general affair';

        $managerDepartmentId = $user->department_id;

        $employeeQuery = Employee::where('id', $id);

        if (!$isHrDepartment) {
            $employeeQuery->where('department_id', $managerDepartmentId);
        }

        $employee = $employeeQuery->firstOrFail();

        $outsourcingList = Outsourcing::orderBy('name')->get();

        $departmentList = $isHrDepartment
            ? Department::orderBy('name')->get()
            : Department::where('id', $managerDepartmentId)->orderBy('name')->get();

        $costCenterList = $isHrDepartment
            ? CostCenter::orderBy('name')->get()
            : CostCenter::where('department_id', $managerDepartmentId)->orderBy('name')->get();

        $psGroupList = $isHrDepartment
            ? PsGroup::orderBy('name')->get()
            : PsGroup::whereHas('costCenter', function ($query) use ($managerDepartmentId) {
                $query->where('department_id', $managerDepartmentId);
            })->orderBy('name')->get();

        $positionList = Position::orderBy('name')->get();

        $levels = Level::orderBy('name')->get();

        return view(
            'pages.manager.employee.edit',
            compact(
                'employee',
                'outsourcingList',
                'departmentList',
                'costCenterList',
                'psGroupList',
                'positionList',
                'levels',
                'isHrDepartment'
            )
        );
    }

    public function managerDetail($id)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');
        $isHrDepartment = $departmentName === 'personalia dan general affair';

        $managerDepartmentId = $user->department_id;

        $employeeQuery = Employee::with([
            'outsourcing',
            'department',
            'costCenter',
            'psGroup',
            'position',
        ])->where('id', $id);

        if (!$isHrDepartment) {
            $employeeQuery->where('department_id', $managerDepartmentId);
        }

        $employee = $employeeQuery->firstOrFail();

        return view(
            'pages.manager.employee.detail',
            compact('employee', 'isHrDepartment')
        );
    }

    public function managerImportPage()
    {
        $outsourcingList = Outsourcing::orderBy('name')->get();

        return view(
            'pages.manager.employee.import',
            compact('outsourcingList')
        );
    }

    public function managerUpload(Request $request)
    {
        $managerDepartmentId = auth()->user()->department_id;

        $request->validate([
            'employment_status' => [
                'required',
                'in:permanent,outsourcing',
            ],

            'outsourcing_id' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'exists:outsourcings,id',
            ],

            'employee_status' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'in:borongan,harian',
            ],

            'file' => [
                'required',
                'mimes:xlsx,xls',
            ],
        ]);

        try {
            if ($request->employment_status === 'permanent') {
                Excel::import(
                    new PermanentEmployeeImport(
                        $request->employment_status,
                        $managerDepartmentId
                    ),
                    $request->file('file')
                );
            } else {
                $outsourcing = Outsourcing::findOrFail(
                    $request->outsourcing_id
                );

                Excel::import(
                    new OutsourcingEmployeeImport(
                        $outsourcing,
                        $request->employee_status,
                        $managerDepartmentId
                    ),
                    $request->file('file')
                );
            }

            return redirect()
                ->route('manager.employee.index')
                ->with('success', 'Import karyawan berhasil.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}