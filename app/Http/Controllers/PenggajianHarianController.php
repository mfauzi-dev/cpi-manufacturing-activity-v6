<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PenggajianHarian;
use App\Models\WageConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenggajianHarianController extends Controller
{
    public function generalManagerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        // Department untuk filter
        $departments = Department::orderBy('name')->get();

        // Query payroll
        $query = PenggajianHarian::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) {
                $q->where('employee_status', 'harian');
            });

        // Filter department
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Grand total
        $grandTotalWorkDays = $query->sum('work_days');
        $grandTotalUpahHarian = $query->sum('upah_harian');
        $grandTotalNetSalary = $query->sum('net_salary');
        
        $payrolls = $query->paginate(10)->withQueryString();
        // UMP dan hari kerja standar
        if ($payrolls->isNotEmpty()) {

            // Gunakan snapshot dari payroll
            $ump = $payrolls->first()->ump_used;
            $hariKerjaStandar = $payrolls->first()->hari_kerja_standar_used;

        } else {

            // Kalau payroll belum ada, ambil dari konfigurasi
            $config = WageConfig::where('tahun', $year)->first();

            $ump = $config->ump ?? 0;
            $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
        }

        // Label periode
        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.general_manager.penggajian-harian.index',
            compact(
                'payrolls',
                'departments',
                'departmentId',
                'month',
                'year',
                'grandTotalWorkDays',
                'grandTotalUpahHarian',
                'grandTotalNetSalary',
                'ump',
                'hariKerjaStandar',
                'periodLabel'
            )
        );
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;

        $payrolls = PenggajianHarian::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'harian')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id');

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        $payrolls = $payrolls->paginate(10)->withQueryString();

        if ($payrolls->isNotEmpty()) {
            $ump = $payrolls->first()->ump_used;
            $hariKerjaStandar = $payrolls->first()->hari_kerja_standar_used;
        } else {
            $config = WageConfig::where('tahun', $year)->first();

            $ump = $config->ump ?? 0;
            $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
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
                'periodLabel'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;

        $payrolls = PenggajianHarian::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'harian')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id');

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        $payrolls = $payrolls->paginate(10)->withQueryString();

        if ($payrolls->isNotEmpty()) {
            $ump = $payrolls->first()->ump_used;
            $hariKerjaStandar = $payrolls->first()->hari_kerja_standar_used;
        } else {
            $config = WageConfig::where('tahun', $year)->first();

            $ump = $config->ump ?? 0;
            $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
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
                'periodLabel'
            )
        );
    }

    public function exportPdfGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        $departments = Department::orderBy('name')->get();

        $query = PenggajianHarian::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) {
                $q->where('employee_status', 'harian');
            });

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $payrolls = $query
            ->orderBy('employee_id')
            ->get();

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        if ($payrolls->isNotEmpty()) {
            $ump = $payrolls->first()->ump_used;
            $hariKerjaStandar = $payrolls->first()->hari_kerja_standar_used;
        } else {
            $config = WageConfig::where('tahun', $year)->first();

            $ump = $config->ump ?? 0;
            $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = 'Semua Department';

        if ($departmentId) {
            $department = Department::find($departmentId);
            $departmentName = $department->name ?? '-';
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
                'departmentName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Harian-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdfManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;

        $payrolls = PenggajianHarian::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'harian')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id')
            ->get();

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        if ($payrolls->isNotEmpty()) {
            $ump = $payrolls->first()->ump_used;
            $hariKerjaStandar = $payrolls->first()->hari_kerja_standar_used;
        } else {
            $config = WageConfig::where('tahun', $year)->first();

            $ump = $config->ump ?? 0;
            $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

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
                'departmentName'
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

        $payrolls = PenggajianHarian::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'harian')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id')
            ->get();

        $grandTotalWorkDays = $payrolls->sum('work_days');
        $grandTotalUpahHarian = $payrolls->sum('upah_harian');
        $grandTotalNetSalary = $payrolls->sum('net_salary');

        if ($payrolls->isNotEmpty()) {
            $ump = $payrolls->first()->ump_used;
            $hariKerjaStandar = $payrolls->first()->hari_kerja_standar_used;
        } else {
            $config = WageConfig::where('tahun', $year)->first();

            $ump = $config->ump ?? 0;
            $hariKerjaStandar = $config->hari_kerja_standar ?? 25;
        }

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

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
                'departmentName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Harian-' . $periodLabel . '.pdf'
        );
    }
}
