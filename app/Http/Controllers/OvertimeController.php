<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Overtime;
use App\Models\OvertimeRate;
use App\Models\PenggajianHarian;
use App\Models\PenggajianKaryawanTetap;
use App\Models\WageConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $departmentName = strtolower($user->department?->name ?? '');

        $query = Overtime::with([
            'employee.department',
            'employee.costCenter',
            'employee.psGroup',
            'employee.position',
            'approver',
        ]);

        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

        $departments = collect();
        $costCenters = collect();

        if ($departmentName === 'general affair') {

            $departments = Department::orderBy('name')->get();

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

        } else {

            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });

            $costCenters = CostCenter::where('department_id', $user->department_id)
                ->orderBy('code')
                ->get();

            if ($costCenterId) {
                $query->whereHas('employee', function ($q) use ($user, $costCenterId) {
                    $q->where('department_id', $user->department_id)
                        ->where('cost_center_id', $costCenterId);
                });
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $overtimes = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($request->size ?? 10)
            ->withQueryString();

        return view('pages.admin_production.overtime.index', compact(
            'overtimes',
            'costCenters',
            'departments'
        ));
    }

    public function show($id)
    {
        $user = auth()->user();

        $departmentName = strtolower($user->department?->name ?? '');

        $overtime = Overtime::with([
            'employee.department',
            'employee.costCenter',
            'employee.psGroup',
            'employee.position',
            'employee.level',
            'approver',
        ])->findOrFail($id);

        if ($departmentName !== 'general affair') {
            if (
                !$overtime->employee ||
                $overtime->employee->department_id !== $user->department_id
            ) {
                abort(403);
            }
        }

        return view(
            'pages.admin_production.overtime.detail',
            compact('overtime')
        );
    }

    public function managerIndex(Request $request)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $query = Overtime::with([
            'employee.department',
            'employee.costCenter',
            'employee.psGroup',
            'employee.position',
            'approver',
        ]);

        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

        if ($departmentName === 'general affair') {
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

            $departments = Department::orderBy('name')->get();
        } else {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });

            if ($costCenterId) {
                $query->whereHas('employee', function ($q) use ($user, $costCenterId) {
                    $q->where('department_id', $user->department_id)
                        ->where('cost_center_id', $costCenterId);
                });
            }

            $departments = collect();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $overtimes = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($request->size ?? 10)
            ->withQueryString();

        return view('pages.manager.overtime.approve', compact(
            'overtimes',
            'departments'
        ));
    }

    public function managerShow($id)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $overtime = Overtime::with([
            'employee.department',
            'employee.costCenter',
            'employee.psGroup',
            'employee.position',
            'employee.level',
            'approver',
        ])->findOrFail($id);

        if ($departmentName !== 'general affair') {
            if (
                !$overtime->employee ||
                $overtime->employee->department_id !== $user->department_id
            ) {
                abort(403);
            }
        }

        return view(
            'pages.manager.overtime.detail',
            compact('overtime')
        );
    }

    public function generalManagerIndex(Request $request)
    {
        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

        $query = Overtime::with([
            'employee.department',
            'employee.costCenter',
            'employee.psGroup',
            'employee.position',
            'approver',
        ]);

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

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $overtimes = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($request->size ?? 10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('pages.general_manager.overtime.approve', compact(
            'overtimes',
            'departments'
        ));
    }

    public function generalManagerShow($id)
    {
        $overtime = Overtime::with([
            'employee.department',
            'employee.costCenter',
            'employee.psGroup',
            'employee.position',
            'employee.level',
            'approver',
        ])->findOrFail($id);

        return view(
            'pages.general_manager.overtime.detail',
            compact('overtime')
        );
    }

    public function getCostCenters($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json($costCenters);
    }

    public function getRate($employeeId, Request $request)
    {
        $employee = Employee::with('level')->findOrFail($employeeId);

        $date = $request->date ?? now()->format('Y-m-d');
        $year = Carbon::parse($date)->year;

        $totalHoursActual = (float) ($request->total_hours_actual ?? 0);
        $totalHoursKonversi = (float) ($request->total_hours_konversi ?? 0);

        $levelNumber = (int) ($employee->level?->name ?? 0);

        if (in_array($employee->employee_status, ['harian', 'harian_kontrak'])) {
            $wageConfig = WageConfig::where('tahun', $year)->first();

            if (!$wageConfig) {
                return response()->json([
                    'success' => false,
                    'rate' => 0,
                    'amount' => 0,
                    'message' => 'Data UMP (WageConfig) untuk tahun tersebut tidak ditemukan.',
                ]);
            }

            $gajiKotor = (float) $wageConfig->ump;
            $hourlyRate = $gajiKotor / 173;
            $overtimeAmount = round($hourlyRate * $totalHoursKonversi, 2);

            return response()->json([
                'success' => true,
                'rate' => round($hourlyRate, 2),
                'amount' => $overtimeAmount,
            ]);
        }

        if (in_array($levelNumber, [5, 6], true)) {
            $employeeSalary = EmployeeSalary::where('employee_id', $employee->id)
                ->where('tahun', $year)
                ->first();

            if (!$employeeSalary) {
                return response()->json([
                    'success' => false,
                    'rate' => 0,
                    'amount' => 0,
                    'message' => 'Data gaji karyawan (EmployeeSalary) untuk tahun tersebut tidak ditemukan.',
                ]);
            }

            $gajiKotor = (float) $employeeSalary->basic_salary;
            $hourlyRate = $gajiKotor / 173;
            $overtimeAmount = round($hourlyRate * $totalHoursKonversi, 2);

            return response()->json([
                'success' => true,
                'rate' => round($hourlyRate, 2),
                'amount' => $overtimeAmount,
            ]);
        }

        $overtimeRate = OvertimeRate::where('tahun', $year)
            ->where('employee_status', $employee->employee_status)
            ->where('level_id', $employee->level_id)
            ->first();

        if (!$overtimeRate) {
            return response()->json([
                'success' => false,
                'rate' => 0,
                'amount' => 0,
                'message' => 'Overtime rate tidak ditemukan untuk karyawan dan tahun tersebut.',
            ]);
        }

        $hourlyRate = (float) $overtimeRate->rate;
        $overtimeAmount = round($hourlyRate * $totalHoursActual, 2);

        return response()->json([
            'success' => true,
            'rate' => round($hourlyRate, 2),
            'amount' => $overtimeAmount,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $query = Employee::with([
            'department',
            'costCenter',
            'psGroup',
            'position',
        ])->where('is_active', true);

        if ($departmentName !== 'general affair') {
            $query->where('department_id', $user->department_id);
        }

        $employees = $query
            ->orderBy('name')
            ->get();

        return view('pages.admin_production.overtime.create', compact(
            'employees'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'total_hours_actual' => ['required', 'numeric', 'min:0'],
            'total_hours_konversi' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $employee = Employee::with('level')->findOrFail($request->employee_id);

        if (
            $departmentName !== 'general affair' &&
            $employee->department_id != $user->department_id
        ) {
            abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
        }

        $year = Carbon::parse($request->date)->year;
        $totalHoursActual = (float) $request->total_hours_actual;
        $totalHoursKonversi = (float) $request->total_hours_konversi;
        $levelNumber = (int) ($employee->level?->name ?? 0);

        if (in_array($employee->employee_status, ['harian', 'harian_kontrak'])) {
            $wageConfig = WageConfig::where('tahun', $year)->first();

            if (!$wageConfig) {
                return back()
                    ->withInput()
                    ->with('error', 'Data UMP (WageConfig) untuk tahun tersebut tidak ditemukan.');
            }

            $gajiKotor = (float) $wageConfig->ump;
            $hourlyRate = $gajiKotor / 173;
            $overtimeAmount = round($hourlyRate * $totalHoursKonversi, 2);
        } elseif (in_array($levelNumber, [5, 6], true)) {
            $employeeSalary = EmployeeSalary::where('employee_id', $employee->id)
                ->where('tahun', $year)
                ->first();

            if (!$employeeSalary) {
                return back()
                    ->withInput()
                    ->with('error', 'Data gaji karyawan (EmployeeSalary) untuk tahun tersebut tidak ditemukan.');
            }

            $gajiKotor = (float) $employeeSalary->basic_salary;
            $hourlyRate = $gajiKotor / 173;
            $overtimeAmount = round($hourlyRate * $totalHoursKonversi, 2);
        } else {
            $overtimeRate = OvertimeRate::where('tahun', $year)
                ->where('employee_status', $employee->employee_status)
                ->where('level_id', $employee->level_id)
                ->first();

            if (!$overtimeRate) {
                return back()
                    ->withInput()
                    ->with('error', 'Overtime rate tidak ditemukan untuk karyawan dan tahun tersebut.');
            }

            $hourlyRate = (float) $overtimeRate->rate;
            $overtimeAmount = round($hourlyRate * $totalHoursActual, 2);
        }

        Overtime::create([
            'employee_id' => $employee->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours_actual' => $totalHoursActual,
            'total_hours_konversi' => $totalHoursKonversi,
            'hourly_rate' => $hourlyRate,
            'overtime_amount' => $overtimeAmount,
            'status' => 'PENDING',
            'description' => $request->description,
        ]);

        return redirect()
            ->route('admin-production.overtime.index')
            ->with('success', 'Overtime berhasil ditambahkan dan menunggu approval.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $overtime = Overtime::with('employee')->findOrFail($id);

        if ($overtime->status !== 'PENDING') {
            return back()->with(
                'error',
                'Overtime yang sudah diproses tidak dapat diedit.'
            );
        }

        if (
            $departmentName !== 'general affair' &&
            $overtime->employee->department_id != $user->department_id
        ) {
            abort(403, 'Anda tidak memiliki akses ke overtime ini.');
        }

        $query = Employee::with([
            'department',
            'costCenter',
            'psGroup',
            'position',
        ])->where('is_active', true);

        if ($departmentName !== 'general affair') {
            $query->where('department_id', $user->department_id);
        }

        $employees = $query
            ->orderBy('name')
            ->get();

        return view('pages.admin_production.overtime.edit', compact(
            'overtime',
            'employees'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'total_hours_actual' => ['required', 'numeric', 'min:0'],
            'total_hours_konversi' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $overtime = Overtime::with('employee')->findOrFail($id);

        if ($overtime->status !== 'PENDING') {
            return back()->with(
                'error',
                'Overtime yang sudah diproses tidak dapat diedit.'
            );
        }

        if (
            $departmentName !== 'general affair' &&
            $overtime->employee->department_id != $user->department_id
        ) {
            abort(403, 'Anda tidak memiliki akses ke overtime ini.');
        }

        $employee = Employee::with('level')->findOrFail($request->employee_id);

        if (
            $departmentName !== 'general affair' &&
            $employee->department_id != $user->department_id
        ) {
            abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
        }

        $year = Carbon::parse($request->date)->year;
        $totalHoursActual = (float) $request->total_hours_actual;
        $totalHoursKonversi = (float) $request->total_hours_konversi;
        $levelNumber = (int) ($employee->level?->name ?? 0);

        if (in_array($employee->employee_status, ['harian', 'harian_kontrak'])) {
            $wageConfig = WageConfig::where('tahun', $year)->first();

            if (!$wageConfig) {
                return back()
                    ->withInput()
                    ->with('error', 'Data UMP (WageConfig) untuk tahun tersebut tidak ditemukan.');
            }

            $gajiKotor = (float) $wageConfig->ump;
            $hourlyRate = $gajiKotor / 173;
            $overtimeAmount = round($hourlyRate * $totalHoursKonversi, 2);
        } elseif (in_array($levelNumber, [5, 6], true)) {
            $employeeSalary = EmployeeSalary::where('employee_id', $employee->id)
                ->where('tahun', $year)
                ->first();

            if (!$employeeSalary) {
                return back()
                    ->withInput()
                    ->with('error', 'Data gaji karyawan (EmployeeSalary) untuk tahun tersebut tidak ditemukan.');
            }

            $gajiKotor = (float) $employeeSalary->basic_salary;
            $hourlyRate = $gajiKotor / 173;
            $overtimeAmount = round($hourlyRate * $totalHoursKonversi, 2);
        } else {
            $overtimeRate = OvertimeRate::where('tahun', $year)
                ->where('employee_status', $employee->employee_status)
                ->where('level_id', $employee->level_id)
                ->first();

            if (!$overtimeRate) {
                return back()
                    ->withInput()
                    ->with('error', 'Overtime rate tidak ditemukan untuk karyawan dan tahun tersebut.');
            }

            $hourlyRate = (float) $overtimeRate->rate;
            $overtimeAmount = round($hourlyRate * $totalHoursActual, 2);
        }

        $overtime->update([
            'employee_id' => $employee->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours_actual' => $totalHoursActual,
            'total_hours_konversi' => $totalHoursKonversi,
            'hourly_rate' => $hourlyRate,
            'overtime_amount' => $overtimeAmount,
            'status' => 'PENDING',
            'approved_by' => null,
            'approved_at' => null,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('admin-production.overtime.index')
            ->with('success', 'Overtime berhasil diperbarui dan kembali menunggu approval.');
    }

    public function managerApprove($id)
    {
        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $overtime = Overtime::with('employee')->findOrFail($id);

        if ($overtime->status !== 'PENDING') {
            return back()->with('error', 'Overtime sudah diproses.');
        }

        if ($departmentName !== 'general affair') {
            if ($overtime->employee->department_id != $user->department_id) {
                abort(403, 'Anda tidak memiliki akses ke overtime ini.');
            }
        }

        DB::transaction(function () use ($overtime, $user) {

            $overtime->update([
                'status' => 'APPROVED',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $employee = Employee::find($overtime->employee_id);

            if (!$employee) {
                return;
            }

            $date = Carbon::parse($overtime->date);
            $month = $date->month;
            $year = $date->year;
            $overtimeAmount = (float) $overtime->overtime_amount;

            if (in_array($employee->employee_status, ['harian', 'harian_kontrak'])) {

                $payroll = PenggajianHarian::where('employee_id', $employee->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if (!$payroll) {
                    return;
                }

                $overtimeTotal = (float) $payroll->overtime_total + $overtimeAmount;

                $grandTotalUpah = (float) $payroll->grand_total_upah;

                $payroll->update([
                    'overtime_total' => $overtimeTotal,
                    'grand_total_upah' => $grandTotalUpah + $overtimeAmount,
                    'net_salary' => $grandTotalUpah + $overtimeAmount,
                ]);

            } elseif ($employee->employee_status === 'cpi') {

                $payroll = PenggajianKaryawanTetap::where('employee_id', $employee->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if (!$payroll) {
                    return;
                }

                $overtimeTotal = (float) $payroll->overtime_total + $overtimeAmount;

                $basicSalary = (float) $payroll->basic_salary;

                $payroll->update([
                    'overtime_total' => $overtimeTotal,
                    'grand_total_salary' => $basicSalary + $overtimeTotal,
                ]);
            }
        });

        return back()->with(
            'success',
            'Overtime berhasil disetujui dan payroll diperbarui.'
        );
    }

    public function managerReject(Request $request, $id)
    {
        $request->validate([
            'description' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $departmentName = strtolower($user->department?->name ?? '');

        $overtime = Overtime::with('employee')->findOrFail($id);

        if ($overtime->status !== 'PENDING') {
            return back()->with('error', 'Overtime sudah diproses.');
        }

        if ($departmentName !== 'general affair') {
            if ($overtime->employee->department_id != $user->department_id) {
                abort(403, 'Anda tidak memiliki akses ke overtime ini.');
            }
        }

        $overtime->update([
            'status' => 'REJECTED',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'description' => $request->description ?: $overtime->description,
        ]);

        return back()->with(
            'success',
            'Overtime berhasil ditolak.'
        );
    }

    public function generalManagerApprove($id)
    {
        $overtime = Overtime::with('employee')->findOrFail($id);

        if ($overtime->status !== 'PENDING') {
            return back()->with('error', 'Overtime sudah diproses.');
        }

        DB::transaction(function () use ($overtime) {

            $overtime->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $employee = Employee::find($overtime->employee_id);

            if (!$employee) {
                return;
            }

            $date = Carbon::parse($overtime->date);
            $month = $date->month;
            $year = $date->year;
            $overtimeAmount = (float) $overtime->overtime_amount;

            if (in_array($employee->employee_status, ['harian', 'harian_kontrak'])) {

                $payroll = PenggajianHarian::where('employee_id', $employee->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if (!$payroll) {
                    return;
                }

                $overtimeTotal = (float) $payroll->overtime_total + $overtimeAmount;

                $grandTotalUpah = (float) $payroll->grand_total_upah;

                $payroll->update([
                    'overtime_total' => $overtimeTotal,
                    'grand_total_upah' => $grandTotalUpah + $overtimeAmount,
                    'net_salary' => $grandTotalUpah + $overtimeAmount,
                ]);

            } elseif ($employee->employee_status === 'cpi') {

                $payroll = PenggajianKaryawanTetap::where('employee_id', $employee->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if (!$payroll) {
                    return;
                }

                $overtimeTotal = (float) $payroll->overtime_total + $overtimeAmount;

                $basicSalary = (float) $payroll->basic_salary;

                $payroll->update([
                    'overtime_total' => $overtimeTotal,
                    'grand_total_salary' => $basicSalary + $overtimeTotal,
                ]);
            }
        });

        return back()->with(
            'success',
            'Overtime berhasil disetujui dan payroll diperbarui.'
        );
    }

    public function generalManagerReject(Request $request, $id)
    {
        $request->validate([
            'description' => ['nullable', 'string'],
        ]);

        $overtime = Overtime::with('employee')->findOrFail($id);

        if ($overtime->status !== 'PENDING') {
            return back()->with('error', 'Overtime sudah diproses.');
        }

        $overtime->update([
            'status' => 'REJECTED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'description' => $request->description ?: $overtime->description,
        ]);

        return back()->with(
            'success',
            'Overtime berhasil ditolak.'
        );
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();
            $departmentName = strtolower($user->department?->name ?? '');

            $overtime = Overtime::with('employee')->findOrFail($id);

            if (
                $departmentName !== 'general affair' &&
                $overtime->employee->department_id != $user->department_id
            ) {
                abort(403, 'Anda tidak memiliki akses ke overtime ini.');
            }

            $employeeId = $overtime->employee_id;
            $employee = $overtime->employee;

            $date = Carbon::parse($overtime->date);
            $month = $date->month;
            $year = $date->year;

            $overtime->delete();

            $approvedOvertimeTotal = Overtime::where('employee_id', $employeeId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('status', ['APPROVED', 'PAID'])
                ->sum('overtime_amount');

            $approvedOvertimeTotal = (float) $approvedOvertimeTotal;

            if (in_array($employee->employee_status, ['harian', 'harian_kontrak'])) {

                $payroll = PenggajianHarian::where('employee_id', $employeeId)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if ($payroll) {
                    $baseGrandTotal = (float) $payroll->grand_total_upah
                        - (float) $payroll->overtime_total;

                    $grandTotalUpah = $baseGrandTotal + $approvedOvertimeTotal;

                    $payroll->update([
                        'overtime_total' => $approvedOvertimeTotal,
                        'grand_total_upah' => $grandTotalUpah,
                        'net_salary' => $grandTotalUpah,
                    ]);
                }

            } elseif ($employee->employee_status === 'cpi') {

                $payroll = PenggajianKaryawanTetap::where('employee_id', $employeeId)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->first();

                if ($payroll) {
                    $basicSalary = (float) $payroll->basic_salary;

                    $grandTotalSalary = $basicSalary + $approvedOvertimeTotal;

                    $payroll->update([
                        'overtime_total' => $approvedOvertimeTotal,
                        'grand_total_salary' => $grandTotalSalary,
                    ]);
                }
            }

            DB::commit();

            return back()->with(
                'success',
                'Overtime berhasil dihapus dan payroll berhasil diperbarui.'
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Gagal menghapus overtime: ' . $e->getMessage()
            );
        }
    }
}