<?php

namespace App\Http\Controllers;

use App\Imports\EmployeeImport;
use App\Imports\OutsourcingEmployeeImport;
use App\Imports\PermanentEmployeeImport;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
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
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 50);

        $employmentStatus = $request->employment_status;
        $employeeStatus = $request->employee_status;
        $costCenterId = $request->cost_center_id;
        $positionId = $request->position_id;

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup',
            'position',
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

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        $employees = $query
            ->latest()
            ->paginate($size)
            ->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();
        $positionList = Position::orderBy('name')->get();

        return view(
            'pages.admin.employee.index',
            compact(
                'employees',
                'search',
                'employmentStatus',
                'employeeStatus',
                'costCenterId',
                'positionId',
                'costCenterList',
                'positionList'
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
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 50);

        $employmentStatus = $request->employment_status;
        $employeeStatus = $request->employee_status;
        $costCenterId = $request->cost_center_id;
        $positionId = $request->position_id;

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup',
            'position',
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

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        $employees = $query
            ->latest()
            ->paginate($size)
            ->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();
        $positionList = Position::orderBy('name')->get();

        return view(
            'pages.general_manager.employee.index',
            compact(
                'employees',
                'search',
                'employmentStatus',
                'employeeStatus',
                'costCenterId',
                'positionId',
                'costCenterList',
                'positionList'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'employment_status' => ['nullable', 'in:permanent,outsourcing'],
            'employee_status' => ['nullable', 'in:cpi,borongan,harian'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 50);

        $employmentStatus = $request->employment_status;
        $employeeStatus = $request->employee_status;
        $costCenterId = $request->cost_center_id;
        $positionId = $request->position_id;

        $query = Employee::with([
            'outsourcing',
            'costCenter',
            'psGroup',
            'position',
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

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        $employees = $query
            ->latest()
            ->paginate($size)
            ->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();
        $positionList = Position::orderBy('name')->get();

        return view(
            'pages.manager.employee.index',
            compact(
                'employees',
                'search',
                'employmentStatus',
                'employeeStatus',
                'costCenterId',
                'positionId',
                'costCenterList',
                'positionList'
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

        $departments = Department::orderBy('name')->get();
 
        return view(
            'pages.admin.employee.create',
            compact(
                'outsourcingList',
                'costCenters',
                'psGroups',
                'positions',
                'departments'
            )
        );
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'nik' => ['nullable', 'string', 'max:50', 'unique:employees,nik'],
            'name' => ['required', 'string', 'max:255'],
 
            'employment_status' => [
                'required',
                'in:permanent,outsourcing'
            ],
 
            // wajib diisi kalau outsourcing, otomatis di-set "cpi" kalau permanent
            'employee_status' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'in:borongan,harian'
            ],
 
            'outsourcing_id' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'exists:outsourcings,id'
            ],
 
            'department_id' => [
                'nullable',
                'exists:departments,id'
            ],

            'cost_center_id' => [
                'nullable',
                'exists:cost_centers,id'
            ],
 
            'ps_group_id' => [
                'nullable',
                'exists:ps_groups,id'
            ],
 
            'position_id' => [
                'nullable',
                'exists:positions,id'
            ],
 
            'personel_area' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
        ]);
 
        // employee_status & outsourcing_id otomatis disesuaikan,
        // biar gak ada data nyangkut yang gak relevan kalau permanent
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
 
            'personel_area' => $request->personel_area,
            'gender' => $request->gender,
        ]);
 
        return redirect()
            ->route('admin.employee.index')
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
                'in:permanent,outsourcing'
            ],

            'outsourcing_id' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'exists:outsourcings,id'
            ],

            'employee_status' => [
                'required_if:employment_status,outsourcing',
                'nullable',
                'in:borongan,harian'
            ],

            'file' => [
                'required',
                'mimes:xlsx,xls'
            ]
        ]);


        try {
            if ($request->employment_status === 'permanent') {
                Excel::import(
                    new PermanentEmployeeImport(),
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

        return view(
            'pages.admin.employee.edit',
            compact(
                'employee',
                'outsourcingList',
                'departmentList',
                'costCenterList',
                'psGroupList',
                'positionList'
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
                'in:cpi,borongan,harian',
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

            'personel_area' => [
                'nullable',
                'string',
            ],

            'gender' => [
                'nullable',
                'string',
            ],
        ]);

        $employee->update([

            'nik' => $request->nik,
            'name' => $request->name,

            'employment_status' => $request->employment_status,

            'outsourcing_id' => $request->employment_status == 'outsourcing'
                ? $request->outsourcing_id
                : null,

            'employee_status' => $request->employment_status == 'outsourcing'
                ? $request->employee_status
                : 'cpi',

            'department_id' => $request->department_id,
            'cost_center_id' => $request->cost_center_id,
            'ps_group_id' => $request->ps_group_id,
            'position_id' => $request->position_id,
            'personel_area' => $request->personel_area,
            'gender' => $request->gender,
        ]);

        return redirect()
            ->route('admin.employee.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
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
}