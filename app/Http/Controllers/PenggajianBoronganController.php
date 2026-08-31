<?php

namespace App\Http\Controllers;

use App\Exports\PenggajianBoronganExport;
use App\Models\Department;
use App\Models\Outsourcing;
use App\Models\PenggajianBorongan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PenggajianBoronganController extends Controller
{
    public function generalManagerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');

        $departments = Department::orderBy('name')->get();
        $outsourcings = Outsourcing::orderBy('name')->get();

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {
                $q->where('employee_status', 'borongan');

                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            });

        $grandTotalKg = (clone $query)->sum('total_kg');
        $grandTotalUpah = (clone $query)->sum('total_upah');

        $payrolls = $query
            ->orderBy('employee_id')
            ->paginate(10)
            ->withQueryString();

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.general_manager.penggajian-borongan.index',
            compact(
                'payrolls',
                'departments',
                'departmentId',
                'outsourcings',
                'outsourcingId',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel'
            )
        );
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $outsourcingId = $request->input('outsourcing_id');

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {

                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            })
            ->orderBy('employee_id');

        $grandTotalKg = (clone $query)->sum('total_kg');
        $grandTotalUpah = (clone $query)->sum('total_upah');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $outsourcings = Outsourcing::orderBy('name')->get();

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.admin_production.penggajian-borongan.index',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'outsourcings',
                'outsourcingId'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            })
            ->orderBy('employee_id');

        $grandTotalKg = (clone $query)->sum('total_kg');
        $grandTotalUpah = (clone $query)->sum('total_upah');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

        $outsourcings = Outsourcing::orderBy('name')->get();

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        return view(
            'pages.manager.penggajian-borongan.index',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'outsourcings',
                'outsourcingId'
            )
        );
    }

    public function exportPdfGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');
        $outsourcingId = $request->input('outsourcing_id');

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing',
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {
                $q->where('employee_status', 'borongan');

                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            });

        $payrolls = $query
            ->orderBy('employee_id')
            ->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

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
            'pages.general_manager.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName',
                'outsourcingName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdfManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.manager.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName',
                'outsourcingName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }

    public function exportPdf(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianBorongan::with([
            'employee.department',
            'employee.outsourcing'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId, $outsourcingId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);

                if ($outsourcingId) {
                    $q->where('outsourcing_id', $outsourcingId);
                }
            })
            ->orderBy('employee_id');

        $payrolls = $query->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $outsourcingName = 'Semua Outsourcing';

        if ($outsourcingId) {
            $outsourcing = Outsourcing::find($outsourcingId);
            $outsourcingName = $outsourcing->name ?? '-';
        }

        $pdf = Pdf::loadView(
            'pages.admin_production.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName',
                'outsourcingName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }

    public function exportExcel(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId,
                $outsourcingId
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
        );
    }

    public function exportExcelManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $outsourcingId = $request->input('outsourcing_id');

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F-Y');

        return Excel::download(
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId,
                $outsourcingId
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
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
            new PenggajianBoronganExport(
                $month,
                $year,
                $departmentId ? (int) $departmentId : null,
                $outsourcingId ? (int) $outsourcingId : null
            ),
            'Penggajian-Borongan-' . $periodLabel . '.xlsx'
        );
    }
}