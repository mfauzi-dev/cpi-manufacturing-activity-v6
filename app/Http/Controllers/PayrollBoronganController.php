<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollBorongan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollBoronganController extends Controller
{
    const BPJS_KESEHATAN_RATE = 0.04;
    const JAMINAN_PENSIUN     = 0.02;
    const JHT                 = 0.0489;
    const MANAGEMENT_FEE_RATE = 6800;

    public function generate(Request $request)
    {

        $request->validate([
        'month' => ['required', 'integer', 'between:1,12'],
            'year'  => ['required', 'integer'],
        ]);

        $month = $request->input('month');
        $year  = $request->input('year');

        DB::beginTransaction();

        try {
            $employees = Employee::whereHas('dailyActivities', function ($q) use ($month, $year) {
                $q->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year);
            })->get();

            foreach ($employees as $employee) {
                $isFinal = PayrollBorongan::where('employee_id', $employee->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->where('status', 'FINAL')
                    ->exists();

                if ($isFinal) {
                    continue;
                }

                $activityIds = DailyActivity::where('employee_id', $employee->id)
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->pluck('id');

                $workDays = DailyActivity::where('employee_id', $employee->id)
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->distinct('tanggal')
                    ->count('tanggal');

                $details = DailyActivityDetail::whereIn('daily_activity_id', $activityIds);

                $totalKg      = $details->sum('output_kg');
                $totalEarning = $details->sum('rupiah');

                $bpjsKesehatan  = $totalEarning * self::BPJS_KESEHATAN_RATE;
                $jaminanPensiun = $totalEarning * self::JAMINAN_PENSIUN;
                $jht            = $totalEarning * self::JHT;
                $managementFee  = $workDays * self::MANAGEMENT_FEE_RATE;
                $netSalary      = $totalEarning - $bpjsKesehatan - $jaminanPensiun - $jht - $managementFee;

                PayrollBorongan::updateOrCreate(
                    [
                        'employee_id'  => $employee->id,
                        'period_month' => $month,
                        'period_year'  => $year,
                    ],
                    [
                        'work_days'       => $workDays,
                        'total_kg'        => $totalKg,
                        'total_earning'   => $totalEarning,
                        'bpjs_kesehatan'  => $bpjsKesehatan,
                        'jaminan_pensiun' => $jaminanPensiun,
                        'management_fee'  => $managementFee,
                        'jht'             => $jht,
                        'net_salary'      => $netSalary,
                        'status'          => 'DRAFT',
                        'generated_by'    => Auth::id(),
                        'generated_at'    => now(),
                    ]
                );
            }

        DB::commit();
    
        return redirect()->route('admin.payroll.borongan.index')
                ->with('success', 'Payroll berhasil di-generate!');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer'],
            'month'  => ['nullable', 'integer', 'between:1,12'],
            'year'   => ['nullable', 'integer'],
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);
        $month  = $request->input('month');
        $year   = $request->input('year');

        $query = PayrollBorongan::selectRaw('
            period_month,
            period_year,
            COUNT(DISTINCT employee_id) as total_karyawan,
            SUM(total_earning) as total_gross,
            SUM(net_salary) as total_net,
            MAX(generated_at) as generated_at,
            MAX(CASE WHEN status = "DRAFT" THEN 1 ELSE 0 END) as has_draft
        ')->groupBy('period_month', 'period_year');

        if ($month) {
            $query->where('period_month', $month);
        }

        if ($year) {
            $query->where('period_year', $year);
        }

        $periods = $query->orderBy('period_year', 'DESC')
            ->orderBy('period_month', 'DESC')
            ->paginate($size)
            ->withQueryString();

        return view('pages.admin.payroll.harian.index', compact('periods', 'search', 'month', 'year', 'size'));
    }

    public function generateForm()
    {
        return view('pages.admin.payroll.harian.generate');
    }

    public function detail($month, $year, Request $request)
    {
        $request->validate([
            'search'        => ['nullable', 'string'],
            'size'          => ['nullable', 'integer'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'month'         => ['nullable', 'integer', 'between:1,12'],
            'year'          => ['nullable', 'integer'],
        ]);

        $departmentId = $request->input('department_id');
        $status       = $request->input('status');
        $size         = $request->input('size', 10);

        $departmentList = Department::orderBy('name')->get();

        $query = PayrollBorongan::with('employee')
            ->where('period_month', $month)
            ->where('period_year', $year);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $payrollHarian = $query->paginate($size)->withQueryString();

        // === Grand total (tidak terpengaruh pagination) ===
        $allPayroll = PayrollBorongan::where('period_month', $month)
            ->where('period_year', $year)
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->get();

        // === List semua tanggal dalam periode ===
        $dates = [];
        $period = CarbonPeriod::create(
            Carbon::create($year, $month)->startOfMonth(),
            Carbon::create($year, $month)->endOfMonth(),
        );
        foreach ($period as $date) {
            $dates[] = $date->toDateString();
        }

        $earningsRaw = DailyActivityDetail::query()
            ->join('daily_activities', 'daily_activities.id', '=', 'daily_activity_details.daily_activity_id')
            ->whereMonth('daily_activities.tanggal', $month)
            ->whereYear('daily_activities.tanggal', $year)
            ->selectRaw('
                daily_activities.employee_id,
                daily_activities.tanggal,
                SUM(daily_activity_details.rupiah) as total_rupiah,
                SUM(daily_activity_details.output_kg) as total_kg
            ')
            ->groupBy('daily_activities.employee_id', 'daily_activities.tanggal')
            ->get();

        $earningsGrouped = [];

        foreach ($earningsRaw as $row) {
            $employeeId = $row->employee_id;
            $dateKey    = Carbon::parse($row->tanggal)->toDateString();

            $earningsGrouped[$employeeId][$dateKey] = [
                'total_rupiah' => $row->total_rupiah,
                'total_kg'     => $row->total_kg,
            ];
        }

        $grandTotal = [
            'total_earning'   => $allPayroll->sum('total_earning'),
            'total_kg'        => $allPayroll->sum('total_kg'),
            'work_days'       => $allPayroll->sum('work_days'),
            'management_fee'  => $allPayroll->sum('management_fee'),
            'bpjs_kesehatan'  => $allPayroll->sum('bpjs_kesehatan'),
            'jaminan_pensiun' => $allPayroll->sum('jaminan_pensiun'),
            'jht'             => $allPayroll->sum('jht'),
            'net_salary'      => $allPayroll->sum('net_salary'),
        ];

        $periodLabel = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        $rates = [
            'management_fee'  => self::MANAGEMENT_FEE_RATE,
            'jht'             => self::JHT * 100,
            'bpjs_kesehatan'  => self::BPJS_KESEHATAN_RATE * 100,
            'jaminan_pensiun' => self::JAMINAN_PENSIUN * 100,
        ];

        return view('pages.admin.payroll.harian.detail', [
            'payrollHarian'   => $payrollHarian,
            'dates'           => $dates,
            'earningsGrouped' => $earningsGrouped,
            'grandTotal'      => $grandTotal,
            'periodLabel'     => $periodLabel,
            'rates'           => $rates,
            'month'           => $month,
            'year'            => $year,
            'departmentId'    => $departmentId,
            'departmentList'  => $departmentList,
            'status'          => $status,
        ]);
    }

    public function finalize($id)
{
    try {
        $payroll = PayrollBorongan::findOrFail($id);

        if ($payroll->status === 'FINAL') {
            throw new \Exception('Payroll ini sudah berstatus final');
        }

        $payroll->update(['status' => 'FINAL']);

        return back()->with('success', 'Payroll berhasil difinalisasi.');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

    public function finalizePeriod($month, $year)
    {
        $updated = PayrollBorongan::where('period_month', $month)
            ->where('period_year', $year)
            ->where('status', 'DRAFT')
            ->update(['status' => 'FINAL']);

        if ($updated === 0) {
            return back()->with('error', 'Tidak ada data DRAFT untuk periode ini.');
        }

        $periodLabel = Carbon::create($year, $month)->translatedFormat('F Y');

        return back()->with('success', 'Semua payroll periode ' . $periodLabel . ' berhasil difinalisasi. (' . $updated . ' data)');
    }

    public function destroyPeriod($month, $year)
    {
        PayrollBorongan::where('period_month', $month)
            ->where('period_year', $year)
            ->where('status', 'DRAFT')
            ->delete();

        $periodLabel = Carbon::create($year, $month)->translatedFormat('F Y');

        return back()->with('success', 'Semua payroll periode ' . $periodLabel . ' berhasil dihapus');
    }
}
