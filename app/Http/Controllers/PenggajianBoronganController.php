<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PenggajianBorongan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenggajianBoronganController extends Controller
{
    public function generalManagerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        $departments = Department::orderBy('name')->get();

        $query = PenggajianBorongan::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) {
                $q->where('employee_status', 'borongan');
            });

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $grandTotalKg = $query->sum('total_kg');
        $grandTotalUpah = $query->sum('total_upah');

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

        $query = PenggajianBorongan::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id');

        $grandTotalKg = $query->sum('total_kg');
        $grandTotalUpah = $query->sum('total_upah');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

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
                'periodLabel'
            )
        );
    }

    public function managerIndex(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $query = PenggajianBorongan::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id');

        $grandTotalKg = $query->sum('total_kg');
        $grandTotalUpah = $query->sum('total_upah');

        $payrolls = $query
            ->paginate(10)
            ->withQueryString();

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
                'periodLabel'
            )
        );
    }

    public function exportPdfGeneralManager(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        $query = PenggajianBorongan::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) {
                $q->where('employee_status', 'borongan');
            });

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

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

        $pdf = Pdf::loadView(
            'pages.general_manager.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName'
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

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $payrolls = PenggajianBorongan::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id')
            ->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $pdf = Pdf::loadView(
            'pages.manager.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName'
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

        $departmentId = Auth::user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $payrolls = PenggajianBorongan::with([
            'employee.department'
        ])
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('employee_status', 'borongan')
                    ->where('department_id', $departmentId);
            })
            ->orderBy('employee_id')
            ->get();

        $grandTotalKg = $payrolls->sum('total_kg');
        $grandTotalUpah = $payrolls->sum('total_upah');

        $periodLabel = Carbon::create($year, $month, 1)
            ->translatedFormat('F Y');

        $departmentName = Auth::user()->department->name ?? '-';

        $pdf = Pdf::loadView(
            'pages.admin_production.penggajian-borongan.pdf',
            compact(
                'payrolls',
                'month',
                'year',
                'grandTotalKg',
                'grandTotalUpah',
                'periodLabel',
                'departmentName'
            )
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Penggajian-Borongan-' . $periodLabel . '.pdf'
        );
    }
}