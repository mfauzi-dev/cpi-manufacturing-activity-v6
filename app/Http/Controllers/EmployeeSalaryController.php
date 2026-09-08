<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\PenggajianKaryawanTetap;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    private function authorizeManagerGeneralAffair()
    {
        $user = auth()->user();

        if (
            !$user ||
            strtolower($user->role?->name ?? '') !== 'manager' ||
            strtolower($user->department?->name ?? '') !== 'personalia dan general affair'
        ) {
            abort(403, 'Anda tidak memiliki akses ke halaman Employee Salary.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeManagerGeneralAffair();

        $request->validate([
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'cost_center_id' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'search' => ['nullable', 'string'],
            'tahun' => ['nullable', 'integer', 'digits:4'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $request->input('search');
        $tahun = $request->input('tahun');
        $departmentId = $request->input('department_id');
        $costCenterId = $request->input('cost_center_id');
        $size = $request->input('size', 10);

        $departments = Department::orderBy('name')->get();

        $costCenters = collect();

        if ($departmentId) {
            $costCenters = CostCenter::where('department_id', $departmentId)
                ->orderBy('name')
                ->get();
        }

        $query = EmployeeSalary::with([
            'employee.department',
            'employee.costCenter',
        ]);

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($costCenterId) {
            $query->whereHas('employee', function ($q) use ($costCenterId) {
                $q->where('cost_center_id', $costCenterId);
            });
        }

        $employeeSalaries = $query
            ->latest('tahun')
            ->latest()
            ->paginate($size)
            ->withQueryString();

        return view('pages.manager.employee-salary.index', compact(
            'employeeSalaries',
            'search',
            'tahun',
            'departments',
            'costCenters',
            'departmentId',
            'costCenterId'
        ));
    }

    public function getCostCenters($departmentId)
    {
        $this->authorizeManagerGeneralAffair();

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return response()->json($costCenters);
    }

    public function create()
    {
        $this->authorizeManagerGeneralAffair();

        $employees = Employee::with('department')
            ->orderBy('name')
            ->get();

        return view('pages.manager.employee-salary.create', compact(
            'employees'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeManagerGeneralAffair();

        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'tahun' => ['required', 'integer', 'digits:4'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        if ($employee->employee_status !== 'cpi') {
            return back()
                ->withInput()
                ->with('error', 'Salary hanya dapat dibuat untuk karyawan tetap.');
        }

        $exists = EmployeeSalary::where('employee_id', $employee->id)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Salary karyawan untuk tahun tersebut sudah tersedia.');
        }

        EmployeeSalary::create([
            'employee_id' => $employee->id,
            'tahun' => $request->tahun,
            'basic_salary' => $request->basic_salary,
        ]);

        if ((int) $request->tahun === now()->year) {
            PenggajianKaryawanTetap::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'period_month' => now()->month,
                    'period_year' => $request->tahun,
                ],
                [
                    'basic_salary' => $request->basic_salary,
                    'overtime_total' => 0,
                    'grand_total_salary' => $request->basic_salary,
                ]
            );
        }

        return redirect()
            ->route('manager.employee-salary.index')
            ->with('success', 'Salary karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->authorizeManagerGeneralAffair();

        $employeeSalary = EmployeeSalary::with('employee.department')
            ->findOrFail($id);

        $employees = Employee::with('department')
            ->orderBy('name')
            ->get();

        return view('pages.manager.employee-salary.edit', compact(
            'employeeSalary',
            'employees'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeManagerGeneralAffair();

        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'tahun' => ['required', 'integer', 'digits:4'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
        ]);

        $employeeSalary = EmployeeSalary::findOrFail($id);
        $employee = Employee::findOrFail($request->employee_id);

        if ($employee->employee_status !== 'cpi') {
            return back()
                ->withInput()
                ->with('error', 'Salary hanya dapat disimpan untuk karyawan tetap.');
        }

        $exists = EmployeeSalary::where('employee_id', $employee->id)
            ->where('tahun', $request->tahun)
            ->where('id', '!=', $employeeSalary->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Salary karyawan untuk tahun tersebut sudah tersedia.');
        }

        $employeeSalary->update([
            'employee_id' => $employee->id,
            'tahun' => $request->tahun,
            'basic_salary' => $request->basic_salary,
        ]);

        if ((int) $request->tahun === now()->year) {
            $payroll = PenggajianKaryawanTetap::where('employee_id', $employee->id)
                ->where('period_month', now()->month)
                ->where('period_year', $request->tahun)
                ->first();

            if ($payroll) {
                $overtimeTotal = (float) $payroll->overtime_total;

                $payroll->update([
                    'basic_salary' => $request->basic_salary,
                    'grand_total_salary' => (float) $request->basic_salary + $overtimeTotal,
                ]);
            } else {
                PenggajianKaryawanTetap::create([
                    'employee_id' => $employee->id,
                    'period_month' => now()->month,
                    'period_year' => $request->tahun,
                    'basic_salary' => $request->basic_salary,
                    'overtime_total' => 0,
                    'grand_total_salary' => $request->basic_salary,
                ]);
            }
        }

        return redirect()
            ->route('manager.employee-salary.index')
            ->with('success', 'Salary karyawan berhasil diupdate.');
    }


    public function destroy($id)
    {
        $this->authorizeManagerGeneralAffair();

        $employeeSalary = EmployeeSalary::findOrFail($id);

        $employeeSalary->delete();

        return redirect()
            ->route('manager.employee-salary.index')
            ->with('success', 'Salary karyawan berhasil dihapus.');
    }
}