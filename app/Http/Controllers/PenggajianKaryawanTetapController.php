<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\EmployeeSalary;
use App\Models\PenggajianKaryawanTetap;
use Illuminate\Http\Request;

class PenggajianKaryawanTetapController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (strtolower($user->role?->name ?? '') !== 'manager') {
            abort(403, 'Anda tidak memiliki akses ke halaman Penggajian Karyawan Tetap.');
        }

        $departmentName = strtolower($user->department?->name ?? '');

        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $departmentId = $request->department_id;
        $costCenterId = $request->cost_center_id;

        $departments = collect();
        $costCenters = collect();

        if ($departmentName === 'general affair') {
            $departments = Department::orderBy('name')->get();

            if ($departmentId) {
                $costCenters = CostCenter::where('department_id', $departmentId)
                    ->orderBy('code')
                    ->get();
            }
        } else {
            $departmentId = $user->department_id;

            $costCenters = CostCenter::where('department_id', $user->department_id)
                ->orderBy('code')
                ->get();
        }

        $salaryQuery = EmployeeSalary::with('employee')
            ->where('tahun', $tahun)
            ->whereHas('employee', function ($q) use (
                $user,
                $departmentName,
                $departmentId,
                $costCenterId
            ) {
                if ($departmentName === 'general affair') {
                    if ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }

                    if ($costCenterId) {
                        $q->where('cost_center_id', $costCenterId);
                    }
                } else {
                    $q->where('department_id', $user->department_id);

                    if ($costCenterId) {
                        $q->where('cost_center_id', $costCenterId);
                    }
                }
            });

        $salaries = $salaryQuery->get();

        foreach ($salaries as $salary) {
            PenggajianKaryawanTetap::firstOrCreate(
                [
                    'employee_id' => $salary->employee_id,
                    'period_month' => $bulan,
                    'period_year' => $tahun,
                ],
                [
                    'basic_salary' => $salary->basic_salary,
                    'overtime_total' => 0,
                    'grand_total_salary' => $salary->basic_salary,
                ]
            );
        }

        $query = PenggajianKaryawanTetap::with([
            'employee.department',
            'employee.costCenter',
        ])
            ->where('period_month', $bulan)
            ->where('period_year', $tahun);

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
        } else {
            $query->whereHas('employee', function ($q) use ($user, $costCenterId) {
                $q->where('department_id', $user->department_id);

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $penggajians = $query
            ->orderBy('id', 'desc')
            ->paginate($request->size ?? 10)
            ->withQueryString();

        return view('pages.manager.penggajian-karyawan-tetap.index', compact(
            'penggajians',
            'costCenters',
            'departments',
            'bulan',
            'tahun',
            'departmentId',
            'costCenterId'
        ));
    }
}
