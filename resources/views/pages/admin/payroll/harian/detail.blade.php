@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Payroll</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.payroll.borongan.index') }}">Payroll</a></div>
            <div class="breadcrumb-item">{{ $periodLabel }}</div>
        </div>
    </div>

    {{-- FLASH MESSAGE --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form method="GET"
                action="{{ route('admin.payroll.borongan.detail', ['month' => $month, 'year' => $year]) }}">

                <div class="row">

                    <div class="col mb-3">
                        <label>Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">Semua Department</option>
                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>

                            <option value="DRAFT" {{ $status == 'DRAFT' ? 'selected' : '' }}>
                                Draft
                            </option>

                            <option value="FINAL" {{ $status == 'FINAL' ? 'selected' : '' }}>
                                Final
                            </option>

                        </select>
                    </div>

                </div>

                {{-- BUTTON DI BAWAH KIRI --}}
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary">
                        Terapkan Filter
                    </button>

                    <a href="{{ route('admin.payroll.borongan.detail', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-secondary">
                        Reset
                    </a>
                </div>

            </form>

        </div>
    </div>


    {{-- SUMMARY CARDS --}}
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Karyawan</h4>
                    </div>
                    <div class="card-body">
                        {{ $payrollHarian->count() }} orang
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Gross</h4>
                    </div>
                    <div class="card-body">
                        Rp {{ number_format($grandTotal['total_earning']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Gaji Bersih</h4>
                    </div>
                    <div class="card-body">
                        Rp {{ number_format($grandTotal['net_salary']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Rekap Payroll — {{ $periodLabel }}</h4>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.payroll.borongan.finalize.period', ['month' => $month, 'year' => $year]) }}"
                    method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-success btn-sm mr-2"
                        onclick="return confirm('Finalisasi semua payroll {{ $periodLabel }}? Status tidak bisa dikembalikan ke DRAFT.')">
                        <i class="fas fa-check-circle"></i> Finalisasi Semua
                    </button>
                </form>

                @if (auth()->user()->role->name === 'SUPER_ADMIN' || auth()->user()->role->name === 'FINANCE')
                    <a href="{{ route('admin.payroll.borongan.export', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                @endif

                <a href="{{ route('admin.payroll.borongan.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 small">
                    <thead>
                        <tr>
                            <th width="40">No.</th>
                            <th>Nama</th>
                            @foreach ($dates as $date)
                                <th class="text-center">{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                            @endforeach
                            <th class="text-center">Total KG</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Hari Kerja</th>
                            <th class="text-center">
                                Management Fee
                                <br><small class="font-weight-normal">{{ $rates['management_fee'] }}</small>
                            </th>
                            <th class="text-center">
                                BPJS Kesehatan
                                <br><small class="font-weight-normal">{{ $rates['bpjs_kesehatan'] }}%</small>
                            </th>
                            <th class="text-center">
                                JAMINAN PENSIUN
                                <br><small class="font-weight-normal">{{ $rates['jaminan_pensiun'] }}%</small>
                            </th>
                            <th class="text-center">
                                JHT
                                <br><small class="font-weight-normal">{{ $rates['jht'] }}%</small>
                            </th>
                            {{-- <th class="text-center">Gaji Bersih</th> --}}
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrollHarian as $i => $payroll)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-weight-bold">{{ $payroll->employee->name }}</td>

                                @foreach ($dates as $date)
                                    @php
                                        $rupiah = $earningsGrouped[$payroll->employee_id][$date]['total_rupiah'] ?? 0;
                                    @endphp
                                    <td class="text-center {{ $rupiah > 0 ? '' : 'text-muted' }}">
                                        {{ $rupiah > 0 ? number_format($rupiah) : '-' }}
                                    </td>
                                @endforeach

                                <td class="text-center">
                                    {{ number_format($payroll->total_kg, 2) }}
                                </td>
                                <td class="text-center font-weight-bold">
                                    Rp {{ number_format($payroll->total_earning) }}
                                </td>
                                <td class="text-center">{{ $payroll->work_days }}</td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format($payroll->management_fee) }}
                                </td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format($payroll->bpjs_kesehatan) }}
                                </td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format($payroll->jaminan_pensiun) }}
                                </td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format($payroll->jht) }}
                                </td>
                                {{-- <td class="text-center font-weight-bold text-success">
                                    Rp {{ number_format($payroll->net_salary) }}
                                </td> --}}
                                <td class="text-center">
                                    <span
                                        class="badge {{ $payroll->status === 'FINAL' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $payroll->status }}
                                    </span>
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    {{-- <a href="{{ route('admin.payroll.borongan.print', $payroll->id) }}"
                                        class="btn btn-primary btn-sm" target="_blank">
                                        Show
                                    </a> --}}

                                    @if ($payroll->status === 'DRAFT')
                                        <form action="{{ route('admin.payroll.borongan.finalize', $payroll->id) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm"
                                                onclick="return confirm('Finalisasi payroll {{ $payroll->employee->name }}?')">
                                                <i class="fas fa-check-circle"></i> Finalisasi
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 10 + count($dates) }}" class="text-center">
                                    Belum ada data payroll untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="2">Grand Total</td>
                            @foreach ($dates as $date)
                                @php
                                    $totalPerDate = 0;
                                    foreach ($earningsGrouped as $employeeData) {
                                        $totalPerDate += $employeeData[$date]['total_rupiah'] ?? 0;
                                    }
                                @endphp
                                <td class="text-center">
                                    {{ number_format($totalPerDate) }}
                                </td>
                            @endforeach
                            <td class="text-center">{{ number_format($grandTotal['total_kg'], 2) }}</td>
                            <td class="text-center">Rp {{ number_format($grandTotal['total_earning']) }}</td>
                            <td class="text-center">{{ $grandTotal['work_days'] }}</td>
                            <td class="text-center text-danger">Rp {{ number_format($grandTotal['management_fee']) }}</td>
                            <td class="text-center text-danger">Rp {{ number_format($grandTotal['bpjs_kesehatan']) }}</td>
                            <td class="text-center text-danger">Rp
                                {{ number_format($grandTotal['jaminan_pensiun']) }}</td>
                            <td class="text-center text-danger">Rp
                                {{ number_format($grandTotal['jht']) }}</td>
                            {{-- <td class="text-center text-success">Rp {{ number_format($grandTotal['net_salary']) }}</td> --}}
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="card-footer text-right">
                    {{ $payrollHarian->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
