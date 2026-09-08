<?php

namespace App\Http\Controllers;

use App\Exports\PenggajianHarianExport;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Outsourcing;
use App\Models\PenggajianHarian;
use App\Models\WageConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PenggajianHarianController extends Controller
{

    public function getCostCenters($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($costCenters);
    }

    public function generalManagerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');

        $departments = Department::orderBy('name')->get();
        $outsourcings = Outsourcing::orderBy('name')->get();

        $query = PenggajianHarian::with([
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId, $search) {

                $q->whereIn('employee_status', ['harian', 'harian_kontrak']);

                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('nik', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $grandTotalWorkDays = (clone $query)->sum('work_days');
        $grandTotalUpahHarian = (clone $query)->sum('upah_harian');
        $grandTotalNetSalary = (clone $query)->sum('net_salary');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $config = WageConfig::where('tahun', $year)->first();

        $ump = $config->ump ?? 0;
        $hariKerjaStandar = $config->hari_kerja_standar ?? 25;

        $snapshot = PenggajianHarian::where('period_month', $month)
            ->where('period_year', $year)
            ->whereNotNull('ump_used')
            ->where('ump_used', '>', 0)
            ->first();

        if ($snapshot) {
            $ump = $snapshot->ump_used;
        }

        $snapshotHariKerja = PenggajianHarian::where('period_month', $month)
            ->where('period_year', $year)
            ->whereNotNull('hari_kerja_standar_used')
            ->where('hari_kerja_standar_used', '>', 0)
            ->first();

        if ($snapshotHariKerja) {
            $hariKerjaStandar = $snapshotHariKerja->hari_kerja_standar_used;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.general_manager.penggajian-harian.index',
            compact(
                'payrolls',
                'departments',
                'outsourcings',
                'departmentId',
                'outsourcingId',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel',
                'costCenterId',
                'search'
            )
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $departmentName = strtolower(trim($user->department?->name ?? ''));

        $isHrDepartment =
            str_contains($departmentName, 'personalia') &&
            str_contains($departmentName, 'general affair');

        $loginDepartmentId = $user->department_id;

        abort_unless(
            $loginDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');
        $search = trim($request->input('search', ''));

        $departmentId = $isHrDepartment
            ? $request->input('department_id')
            : $loginDepartmentId;

        $query = PenggajianHarian::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use (
                $departmentId,
                $isHrDepartment,
                $loginDepartmentId,
                $outsourcingId,
                $costCenterId,
                $search
            ) {
                $q->whereIn('employee_status', [
                    'harian',
                    'harian_kontrak',
                ]);

                if ($isHrDepartment) {
                    if ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }
                } else {
                    $q->where('department_id', $loginDepartmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }

                if ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('nik', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $grandTotalWorkDays = (clone $query)->sum('work_days');
        $grandTotalUpahHarian = (clone $query)->sum('upah_harian');
        $grandTotalNetSalary = (clone $query)->sum('net_salary');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $outsourcings = Outsourcing::orderBy('name')->get();

        if ($isHrDepartment) {
            $departments = Department::orderBy('name')->get();

            $costCenters = $departmentId
                ? CostCenter::where('department_id', $departmentId)
                    ->orderBy('name')
                    ->get()
                : CostCenter::orderBy('name')->get();
        } else {
            $departments = collect();

            $costCenters = CostCenter::where(
                'department_id',
                $loginDepartmentId
            )
                ->orderBy('name')
                ->get();
        }

        $config = WageConfig::where('tahun', $year)->first();

        $ump = $config->ump ?? 0;
        $hariKerjaStandar = $config->hari_kerja_standar ?? 25;

        $snapshot = PenggajianHarian::where('period_month', $month)
            ->where('period_year', $year)
            ->whereNotNull('ump_used')
            ->where('ump_used', '>', 0)
            ->first();

        if ($snapshot) {
            $ump = $snapshot->ump_used;
        }

        $snapshotHariKerja = PenggajianHarian::where('period_month', $month)
            ->where('period_year', $year)
            ->whereNotNull('hari_kerja_standar_used')
            ->where('hari_kerja_standar_used', '>', 0)
            ->first();

        if ($snapshotHariKerja) {
            $hariKerjaStandar = $snapshotHariKerja->hari_kerja_standar_used;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.admin_production.penggajian-harian.index',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel',
                'outsourcings',
                'outsourcingId',
                'costCenterId',
                'departmentId',
                'search',
                'departments',
                'costCenters',
                'isHrDepartment'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $user = Auth::user();
        $departmentName = strtolower($user->department?->name ?? '');
        $isHrDepartment = $departmentName === 'personalia dan general affair';

        $managerDepartmentId = $user->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $search = $request->input('search');
        $departmentId = $isHrDepartment ? $request->input('department_id') : $managerDepartmentId;
        $costCenterId = $request->input('cost_center_id');

        $query = PenggajianHarian::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId, $search, $isHrDepartment, $managerDepartmentId) {

                $q->whereIn('employee_status', ['harian', 'harian_kontrak']);

                if ($isHrDepartment) {
                    if ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }
                } else {
                    $q->where('department_id', $managerDepartmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }

                if ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('nik', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $grandTotalWorkDays = (clone $query)->sum('work_days');
        $grandTotalUpahHarian = (clone $query)->sum('upah_harian');
        $grandTotalNetSalary = (clone $query)->sum('net_salary');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $outsourcings = Outsourcing::orderBy('name')->get();

        $departments = $isHrDepartment
            ? Department::orderBy('name')->get()
            : collect();

        $costCenters = $isHrDepartment
            ? CostCenter::orderBy('name')->get()
            : CostCenter::where('department_id', $managerDepartmentId)->orderBy('name')->get();

        $config = WageConfig::where('tahun', $year)->first();

        $ump = $config->ump ?? 0;
        $hariKerjaStandar = $config->hari_kerja_standar ?? 25;

        $snapshot = PenggajianHarian::where('period_month', $month)
            ->where('period_year', $year)
            ->whereNotNull('ump_used')
            ->where('ump_used', '>', 0)
            ->first();

        if ($snapshot) {
            $ump = $snapshot->ump_used;
        }

        $snapshotHariKerja = PenggajianHarian::where('period_month', $month)
            ->where('period_year', $year)
            ->whereNotNull('hari_kerja_standar_used')
            ->where('hari_kerja_standar_used', '>', 0)
            ->first();

        if ($snapshotHariKerja) {
            $hariKerjaStandar = $snapshotHariKerja->hari_kerja_standar_used;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.manager.penggajian-harian.index',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel',
                'outsourcings',
                'outsourcingId',
                'search',
                'departments',
                'departmentId',
                'costCenters',
                'costCenterId',
                'isHrDepartment'
            )
        );
    }

    public function exportPdfGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');

        $query = PenggajianHarian::with([
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {

                $q->whereIn('employee_status', ['harian', 'harian_kontrak']);

                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        $config = WageConfig::where('tahun', $year)->first();

        $ump = $config->ump ?? 0;
        $hariKerjaStandar = $config->hari_kerja_standar ?? 25;

        $snapshot = $payrolls
            ->where('ump_used', '>', 0)
            ->first();

        if ($snapshot) {
            $ump = $snapshot->ump_used;
        }

        $snapshotHariKerja = $payrolls
            ->where('hari_kerja_standar_used', '>', 0)
            ->first();

        if ($snapshotHariKerja) {
            $hariKerjaStandar = $snapshotHariKerja->hari_kerja_standar_used;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = 'Semua Department';

        if ($departmentId) {
            $department = Department::find($departmentId);
            $departmentName = $department->name ?? '-';
        }

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.general_manager.penggajian-harian.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel',
                'departmentName',
                'outsourcingName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Harian-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdfManager(Request $request)
    {
        $user = Auth::user();
        $departmentName = strtolower($user->department?->name ?? '');
        $isHrDepartment = $departmentName === 'personalia dan general affair';

        $managerDepartmentId = $user->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $search = $request->input('search');
        $departmentId = $isHrDepartment ? $request->input('department_id') : $managerDepartmentId;
        $costCenterId = $request->input('cost_center_id');

        $query = PenggajianHarian::with([
            'employee.department',
            'employee.outsourcing',
            'employee.costCenter',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId, $search, $isHrDepartment, $managerDepartmentId) {

                $q->whereIn('employee_status', ['harian', 'harian_kontrak']);

                if ($isHrDepartment) {
                    if ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }
                } else {
                    $q->where('department_id', $managerDepartmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }

                if ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('nik', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        $config = WageConfig::where('tahun', $year)->first();

        $ump = $config->ump ?? 0;
        $hariKerjaStandar = $config->hari_kerja_standar ?? 25;

        $snapshot = $payrolls
            ->where('ump_used', '>', 0)
            ->first();

        if ($snapshot) {
            $ump = $snapshot->ump_used;
        }

        $snapshotHariKerja = $payrolls
            ->where('hari_kerja_standar_used', '>', 0)
            ->first();

        if ($snapshotHariKerja) {
            $hariKerjaStandar = $snapshotHariKerja->hari_kerja_standar_used;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = $isHrDepartment
            ? ($departmentId ? (Department::find($departmentId)->name ?? '-') : 'Semua Department')
            : ($user->department->name ?? '-');

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.manager.penggajian-harian.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel',
                'departmentName',
                'outsourcingName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Harian-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdf(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianHarian::with([
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId, $costCenterId) {

                $q->whereIn('employee_status', ['harian', 'harian_kontrak'])
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }

                if ($costCenterId) {
                    $q->where('cost_center_id', $costCenterId);
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        $config = WageConfig::where('tahun', $year)->first();

        $ump = $config->ump ?? 0;
        $hariKerjaStandar = $config->hari_kerja_standar ?? 25;

        $snapshot = $payrolls
            ->where('ump_used', '>', 0)
            ->first();

        if ($snapshot) {
            $ump = $snapshot->ump_used;
        }

        $snapshotHariKerja = $payrolls
            ->where('hari_kerja_standar_used', '>', 0)
            ->first();

        if ($snapshotHariKerja) {
            $hariKerjaStandar = $snapshotHariKerja->hari_kerja_standar_used;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.admin_production.penggajian-harian.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel',
                'departmentName',
                'outsourcingName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Harian-' . $periodLabel . '.pdf'
        );
    }

    public function exportExcel(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');
        $costCenterId = $request->input('cost_center_id');

        $departmentId = Auth::user()->department_id;
        

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianHarianExport(
                $month,
                $year,
                $departmentId,
                $outsourcingId,
                $costCenterId
            ),
            'Penggajian-Harian-' . $periodLabel . '.xlsx'
        );
    }

    public function exportExcelManager(Request $request)
    {
        $user = Auth::user();
        $departmentName = strtolower($user->department?->name ?? '');
        $isHrDepartment = $departmentName === 'personalia dan general affair';

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');

        $managerDepartmentId = $user->department_id;

        abort_unless(
            $managerDepartmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $departmentId = $isHrDepartment
            ? ($request->input('department_id') ? (int) $request->input('department_id') : null)
            : $managerDepartmentId;

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianHarianExport(
                $month,
                $year,
                $departmentId,
                $outsourcingId
            ),
            'Penggajian-Harian-' . $periodLabel . '.xlsx'
        );
    }

    public function exportExcelGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianHarianExport(
                $month,
                $year,
                $departmentId ? (int) $departmentId : null,
                $outsourcingId ? (int) $outsourcingId : null
            ),
            'Penggajian-Harian-' . $periodLabel . '.xlsx'
        );
    }
}