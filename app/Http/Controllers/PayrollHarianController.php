<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollHarianController extends Controller
{

    // const BPJS_KESEHATAN_RATE  = 0.01;
    // const JAMINAN_PENSIUN_RATE = 0.02;
    // const JHT_RATE             = 0.0489;
    // const MANAGEMENT_FEE_RATE  = 6800;
    // const STANDARD_DAYS        = 25;
    // const DAILY_SALARY         = 211000;
    
    // public function generate(int $month, int $year)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $employees = Employee::where('employee_status', 'harian')
    //             ->whereHas('attendances', function ($q) use ($month, $year) {
    //                 $q->whereMonth('date', $month)
    //                   ->whereYear('date', $year);
    //             })->get();

    //         foreach ($employees as $employee) {
    //             $isFinal = PayrollHarian::where('employee_id', $employee->id)
    //                 ->where('month', $month)
    //                 ->where('year', $year)
    //                 ->where('status', 'FINAL')
    //                 ->exists();
                
    //             if ($isFinal) {
    //                 continue;
    //             }

    //             $workDays = Attendance::where('employee_id', $employee->id)
    //                 ->whereMonth('date', $month)
    //                 ->whereYear('date', $year)
    //                 ->where('status', 'hadir')
    //                 ->count();

    //             $basicSalary = self
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }
}
