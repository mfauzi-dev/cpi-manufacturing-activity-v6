<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeProductivityExport;
use App\Models\CostCenter;
use App\Models\DailyActivity;
use App\Models\DailyActivityFurther;
use App\Models\DailyActivitySlaughterHouse;
use App\Models\Department;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeProductivityController extends Controller
{
    public function getCostCenters($departmentId)
    {
        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return response()->json($costCenters);
    }

    public function list(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');

        $isFurtherProcessing = strtolower(trim($department->name)) === 'further processing';

        $employeeStatus = $isFurtherProcessing ? 'harian' : 'borongan';

        $employees = Employee::with([
            'department',
            'costCenter',
        ])
            ->where('department_id', $departmentId)
            ->where('employee_status', $employeeStatus)
            ->when($costCenterId, function ($query) use ($costCenterId) {
                $query->where('cost_center_id', $costCenterId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Sausage';
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

            $dailyActivityFurthers = DailyActivityFurther::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivityFurthers as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Further Processing';
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = null;

                    $allDetails->push($detail);
                }
            }

            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Slaughter House';
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $allDetails = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        return view(
            'pages.admin_production.employee_productivity.list',
            compact(
                'department',
                'employees',
                'costCenters',
                'costCenterId',
                'search',
                'allDetails'
            )
        );
    }

    public function managerList(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $managerDepartment = Department::findOrFail($departmentId);

        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');

        $isFurtherProcessing = strtolower(trim($managerDepartment->name)) === 'further processing';

        $employeeStatus = $isFurtherProcessing ? 'harian' : 'borongan';

        $employees = Employee::with([
            'department',
            'costCenter',
        ])
            ->where('department_id', $departmentId)
            ->where('employee_status', $employeeStatus)
            ->when($costCenterId, function ($query) use ($costCenterId) {
                $query->where('cost_center_id', $costCenterId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Sausage';
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

            $dailyActivityFurthers = DailyActivityFurther::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivityFurthers as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Further Processing';
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = null;

                    $allDetails->push($detail);
                }
            }

            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Slaughter House';
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $allDetails = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        return view(
            'pages.manager.employee-productivity.list',
            compact(
                'managerDepartment',
                'employees',
                'costCenters',
                'costCenterId',
                'search',
                'allDetails'
            )
        );
    }

    public function generalManagerList(Request $request)
    {
        $departmentId = $request->input('department_id');
        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');

        $query = Employee::with([
            'department',
            'costCenter',
        ])->where(function ($query) use ($departmentId) {
            if ($departmentId) {
                $department = Department::find($departmentId);

                if ($department) {
                    $isFurtherProcessing =
                        strtolower(trim($department->name)) === 'further processing';

                    $query->where('department_id', $departmentId)
                        ->where(
                            'employee_status',
                            $isFurtherProcessing ? 'harian' : 'borongan'
                        );
                }
            } else {
                $query->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('employee_status', 'harian')
                            ->whereHas('department', function ($department) {
                                $department->whereRaw(
                                    'LOWER(TRIM(name)) = ?',
                                    ['further processing']
                                );
                            });
                    })
                    ->orWhere(function ($q2) {
                        $q2->where('employee_status', 'borongan')
                            ->whereDoesntHave('department', function ($department) {
                                $department->whereRaw(
                                    'LOWER(TRIM(name)) = ?',
                                    ['further processing']
                                );
                            });
                    });
                });
            }
        });

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        $employees = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Sausage';
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

            $dailyActivityFurthers = DailyActivityFurther::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivityFurthers as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Further Processing';
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = null;

                    $allDetails->push($detail);
                }
            }

            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where(
                'employee_id',
                $employee->id
            )
                ->with('details.product')
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->activity_type = 'Slaughter House';
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $allDetails = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        return view(
            'pages.general_manager.employee-productivity.list',
            compact(
                'employees',
                'departments',
                'departmentId',
                'costCenterId',
                'search',
                'allDetails'
            )
        );
    }

    public function detail(Request $request, $employee_id)
    {
        $departmentId = auth()->user()->department_id;

        $employee = Employee::with([
            'department',
            'costCenter',
        ])
            ->where('department_id', $departmentId)
            ->findOrFail($employee_id);

        $from = $request->input('from');
        $to = $request->input('to');

        $allDetails = collect();

        $dailyActivities = DailyActivity::where(
            'employee_id',
            $employee_id
        )
            ->with('details.product')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            })
            ->get();

        foreach ($dailyActivities as $activity) {
            foreach ($activity->details as $detail) {
                $detail->activity_date = $activity->tanggal;
                $detail->display_productivity = $detail->productivity;
                $detail->display_productivity_actual = null;
                $detail->display_total_harga = $detail->total_harga;

                $allDetails->push($detail);
            }
        }

        $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where(
            'employee_id',
            $employee_id
        )
            ->with('details.product')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            })
            ->get();

        foreach ($dailyActivitySlaughterHouses as $activity) {
            foreach ($activity->details as $detail) {
                $detail->activity_date = $activity->tanggal;
                $detail->display_productivity = null;
                $detail->display_productivity_actual = $detail->productivity_actual;
                $detail->display_total_harga = $detail->total_harga;

                $allDetails->push($detail);
            }
        }

        $allDetails = $allDetails
            ->sortByDesc('activity_date')
            ->values();

        return view(
            'pages.admin_production.employee_productivity.detail',
            compact(
                'employee',
                'allDetails'
            )
        );
    }

    public function managerDetail(Request $request, $employee_id)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $employee = Employee::with([
            'department',
            'costCenter',
        ])
            ->where('department_id', $departmentId)
            ->findOrFail($employee_id);

        $from = $request->input('from');
        $to = $request->input('to');

        $allDetails = collect();

        $dailyActivities = DailyActivity::where(
            'employee_id',
            $employee_id
        )
            ->with('details.product')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            })
            ->get();

        foreach ($dailyActivities as $activity) {
            foreach ($activity->details as $detail) {
                $detail->activity_date = $activity->tanggal;
                $detail->display_productivity = $detail->productivity;
                $detail->display_productivity_actual = null;
                $detail->display_total_harga = $detail->total_harga;

                $allDetails->push($detail);
            }
        }

        $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where(
            'employee_id',
            $employee_id
        )
            ->with('details.product')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            })
            ->get();

        foreach ($dailyActivitySlaughterHouses as $activity) {
            foreach ($activity->details as $detail) {
                $detail->activity_date = $activity->tanggal;
                $detail->display_productivity = null;
                $detail->display_productivity_actual = $detail->productivity_actual;
                $detail->display_total_harga = $detail->total_harga;

                $allDetails->push($detail);
            }
        }

        $allDetails = $allDetails
            ->sortByDesc('activity_date')
            ->values();

        return view(
            'pages.manager.employee-productivity.detail',
            compact(
                'employee',
                'allDetails'
            )
        );
    }

    public function generalManagerDetail(Request $request, $employee_id)
    {
        $employee = Employee::with([
            'department',
            'costCenter',
        ])->findOrFail($employee_id);

        $from = $request->input('from');
        $to = $request->input('to');

        $allDetails = collect();

        $dailyActivities = DailyActivity::where(
            'employee_id',
            $employee_id
        )
            ->with('details.product')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            })
            ->get();

        foreach ($dailyActivities as $activity) {
            foreach ($activity->details as $detail) {
                $detail->activity_date = $activity->tanggal;
                $detail->display_productivity = $detail->productivity;
                $detail->display_productivity_actual = null;
                $detail->display_total_harga = $detail->total_harga;

                $allDetails->push($detail);
            }
        }

        $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where(
            'employee_id',
            $employee_id
        )
            ->with('details.product')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            })
            ->get();

        foreach ($dailyActivitySlaughterHouses as $activity) {
            foreach ($activity->details as $detail) {
                $detail->activity_date = $activity->tanggal;
                $detail->display_productivity = null;
                $detail->display_productivity_actual = $detail->productivity_actual;
                $detail->display_total_harga = $detail->total_harga;

                $allDetails->push($detail);
            }
        }

        $allDetails = $allDetails
            ->sortByDesc('activity_date')
            ->values();

        return view(
            'pages.general_manager.employee-productivity.detail',
            compact(
                'employee',
                'allDetails'
            )
        );
    }

    public function exportExcel(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');
        $employeeId = $request->input('employee_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $isFurtherProcessing = strtolower(trim($department->name)) === 'further processing';
        $employeeStatus = $isFurtherProcessing ? 'harian' : 'borongan';

        $employeesQuery = Employee::with(['department', 'costCenter'])
            ->where('department_id', $departmentId)
            ->where('employee_status', $employeeStatus);

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        } else {
            $employeesQuery
                ->when($costCenterId, function ($query) use ($costCenterId) {
                    $query->where('cost_center_id', $costCenterId);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('nik', 'like', '%' . $search . '%');
                    });
                });
        }

        $employees = $employeesQuery->orderBy('name')->get();

        if ($employeeId && $employees->isEmpty()) {
            abort(404, 'Karyawan tidak ditemukan di department Anda.');
        }

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $allDetails = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        return Excel::download(
            new EmployeeProductivityExport($allDetails),
            'Produktivitas-Karyawan.xlsx'
        );
    }

    public function exportExcelManager(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');
        $employeeId = $request->input('employee_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $isFurtherProcessing = strtolower(trim($department->name)) === 'further processing';
        $employeeStatus = $isFurtherProcessing ? 'harian' : 'borongan';

        $employeesQuery = Employee::with(['department', 'costCenter'])
            ->where('department_id', $departmentId)
            ->where('employee_status', $employeeStatus);

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        } else {
            $employeesQuery
                ->when($costCenterId, function ($query) use ($costCenterId) {
                    $query->where('cost_center_id', $costCenterId);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('nik', 'like', '%' . $search . '%');
                    });
                });
        }

        $employees = $employeesQuery->orderBy('name')->get();

        if ($employeeId && $employees->isEmpty()) {
            abort(404, 'Karyawan tidak ditemukan di department Anda.');
        }

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $allDetails = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        return Excel::download(
            new EmployeeProductivityExport($allDetails),
            'Produktivitas-Karyawan.xlsx'
        );
    }

    public function exportExcelGeneralManager(Request $request)
    {
        $departmentId = $request->input('department_id');
        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');
        $employeeId = $request->input('employee_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Employee::with([
            'department',
            'costCenter',
        ])->where(function ($query) use ($departmentId) {
            if ($departmentId) {
                $department = Department::find($departmentId);

                if ($department) {
                    $isFurtherProcessing =
                        strtolower(trim($department->name)) === 'further processing';

                    $query->where('department_id', $departmentId)
                        ->where(
                            'employee_status',
                            $isFurtherProcessing ? 'harian' : 'borongan'
                        );
                }
            } else {
                $query->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('employee_status', 'harian')
                            ->whereHas('department', function ($department) {
                                $department->whereRaw(
                                    'LOWER(TRIM(name)) = ?',
                                    ['further processing']
                                );
                            });
                    })
                    ->orWhere(function ($q2) {
                        $q2->where('employee_status', 'borongan')
                            ->whereDoesntHave('department', function ($department) {
                                $department->whereRaw(
                                    'LOWER(TRIM(name)) = ?',
                                    ['further processing']
                                );
                            });
                    });
                });
            }
        });

        if ($employeeId) {
            $query->where('id', $employeeId);
        } else {
            if ($costCenterId) {
                $query->where('cost_center_id', $costCenterId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%');
                });
            }
        }

        $employees = $query->orderBy('name')->get();

        if ($employeeId && $employees->isEmpty()) {
            abort(404, 'Karyawan tidak ditemukan.');
        }

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

$dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $allDetails = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        return Excel::download(
            new EmployeeProductivityExport($allDetails),
            'Produktivitas-Karyawan.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');
        $employeeId = $request->input('employee_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $isFurtherProcessing = strtolower(trim($department->name)) === 'further processing';
        $employeeStatus = $isFurtherProcessing ? 'harian' : 'borongan';

        $employeesQuery = Employee::with(['department', 'costCenter'])
            ->where('department_id', $departmentId)
            ->where('employee_status', $employeeStatus);

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        } else {
            $employeesQuery
                ->when($costCenterId, function ($query) use ($costCenterId) {
                    $query->where('cost_center_id', $costCenterId);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('nik', 'like', '%' . $search . '%');
                    });
                });
        }

        $employees = $employeesQuery->orderBy('name')->get();

        if ($employeeId && $employees->isEmpty()) {
            abort(404, 'Karyawan tidak ditemukan di department Anda.');
        }

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $data = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        $fromDate = $from ? \Carbon\Carbon::parse($from)->format('d M Y') : '-';
        $toDate = $to ? \Carbon\Carbon::parse($to)->format('d M Y') : '-';

        $pdf = Pdf::loadView(
            'pages.admin_production.employee_productivity.pdf',
            compact('data', 'fromDate', 'toDate')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Produktivitas-Karyawan.pdf');
    }

    public function exportPdfManager(Request $request)
    {
        $departmentId = auth()->user()->department_id;

        abort_unless(
            $departmentId,
            403,
            'Akun Anda belum terhubung ke department manapun.'
        );

        $department = Department::findOrFail($departmentId);

        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');
        $employeeId = $request->input('employee_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $isFurtherProcessing = strtolower(trim($department->name)) === 'further processing';
        $employeeStatus = $isFurtherProcessing ? 'harian' : 'borongan';

        $employeesQuery = Employee::with(['department', 'costCenter'])
            ->where('department_id', $departmentId)
            ->where('employee_status', $employeeStatus);

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        } else {
            $employeesQuery
                ->when($costCenterId, function ($query) use ($costCenterId) {
                    $query->where('cost_center_id', $costCenterId);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('nik', 'like', '%' . $search . '%');
                    });
                });
        }

        $employees = $employeesQuery->orderBy('name')->get();

        if ($employeeId && $employees->isEmpty()) {
            abort(404, 'Karyawan tidak ditemukan di department Anda.');
        }

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

  $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $data = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        $fromDate = $from ? \Carbon\Carbon::parse($from)->format('d M Y') : '-';
        $toDate = $to ? \Carbon\Carbon::parse($to)->format('d M Y') : '-';

        $pdf = Pdf::loadView(
            'pages.manager.employee_productivity.pdf',
            compact('data', 'fromDate', 'toDate')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Produktivitas-Karyawan.pdf');
    }

    public function exportPdfGeneralManager(Request $request)
    {
        $departmentId = $request->input('department_id');
        $costCenterId = $request->input('cost_center_id');
        $search = $request->input('search');
        $employeeId = $request->input('employee_id');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Employee::with(['department', 'costCenter'])->where(function ($query) use ($departmentId) {
            if ($departmentId) {
                $department = Department::find($departmentId);

                if ($department) {
                    $isFurtherProcessing =
                        strtolower(trim($department->name)) === 'further processing';

                    $query->where('department_id', $departmentId)
                        ->where(
                            'employee_status',
                            $isFurtherProcessing ? 'harian' : 'borongan'
                        );
                }
            } else {
                $query->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('employee_status', 'harian')
                            ->whereHas('department', function ($department) {
                                $department->whereRaw(
                                    'LOWER(TRIM(name)) = ?',
                                    ['further processing']
                                );
                            });
                    })
                    ->orWhere(function ($q2) {
                        $q2->where('employee_status', 'borongan')
                            ->whereDoesntHave('department', function ($department) {
                                $department->whereRaw(
                                    'LOWER(TRIM(name)) = ?',
                                    ['further processing']
                                );
                            });
                    });
                });
            }
        });

        if ($employeeId) {
            $query->where('id', $employeeId);
        } else {
            if ($costCenterId) {
                $query->where('cost_center_id', $costCenterId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%');
                });
            }
        }

        $employees = $query->orderBy('name')->get();

        if ($employeeId && $employees->isEmpty()) {
            abort(404, 'Karyawan tidak ditemukan.');
        }

        $allDetails = collect();

        foreach ($employees as $employee) {
            $dailyActivities = DailyActivity::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivities as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = $detail->productivity;
                    $detail->display_productivity_actual = null;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }

            $dailyActivitySlaughterHouses = DailyActivitySlaughterHouse::where('employee_id', $employee->id)
                ->with('details.product')
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('tanggal', [$from, $to]);
                })
                ->get();

            foreach ($dailyActivitySlaughterHouses as $activity) {
                foreach ($activity->details as $detail) {
                    $detail->employee = $employee;
                    $detail->activity_date = $activity->tanggal;
                    $detail->display_productivity = null;
                    $detail->display_productivity_actual = $detail->productivity_actual;
                    $detail->display_total_harga = $detail->total_harga;

                    $allDetails->push($detail);
                }
            }
        }

        $data = $allDetails
            ->sortBy([
                ['employee.name', 'asc'],
                ['activity_date', 'desc'],
            ])
            ->values();

        $fromDate = $from ? \Carbon\Carbon::parse($from)->format('d M Y') : '-';
        $toDate = $to ? \Carbon\Carbon::parse($to)->format('d M Y') : '-';

        $pdf = Pdf::loadView(
            'pages.general_manager.employee-productivity.pdf',
            compact('data', 'fromDate', 'toDate')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Produktivitas-Karyawan.pdf');
    }
}