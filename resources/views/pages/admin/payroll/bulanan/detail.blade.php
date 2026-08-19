@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Payroll Bulanan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.payroll.bulanan.index') }}">Payroll Bulanan</a></div>
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
                        {{ $payrollBulanan->count() }} orang
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
                        Rp {{ number_format($grandTotal['gross_salary']) }}
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-minus-circle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Potongan</h4>
                    </div>
                    <div class="card-body">
                        Rp {{ number_format($grandTotal['deduction_total']) }}
                    </div>
                </div>
            </div>
        </div> --}}
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
            <h4>Rekap Payroll Bulanan — {{ $periodLabel }}</h4>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.payroll.bulanan.finalize.period', ['month' => $month, 'year' => $year]) }}"
                    method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-success btn-sm mr-2"
                        onclick="return confirm('Finalisasi semua payroll {{ $periodLabel }}? Status tidak bisa dikembalikan ke DRAFT.')">
                        <i class="fas fa-check-circle"></i> Finalisasi Semua
                    </button>
                </form>

                @if (auth()->user()->role->name === 'SUPER_ADMIN' || auth()->user()->role->name === 'FINANCE')
                    <a href="{{ route('admin.payroll.bulanan.export', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                @endif

                <a href="{{ route('admin.payroll.bulanan.index') }}" class="btn btn-secondary btn-sm">
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
                            <th class="text-center">Gaji Pokok</th>
                            <th class="text-center">Overtime</th>
                            <th class="text-center">Bonus</th>
                            <th class="text-center">Gross Salary</th>
                            <th class="text-center">
                                BPJS Kesehatan
                                <br><small class="font-weight-normal">{{ $rates['bpjs_kesehatan'] }}%</small>
                            </th>
                            <th class="text-center">
                                BPJS Ketenagakerjaan
                                <br><small class="font-weight-normal">{{ $rates['bpjs_ketenagakerjaan'] }}%</small>
                            </th>
                            <th class="text-center">
                                Management Fee
                                <br><small class="font-weight-normal">{{ $rates['management_fee'] }}%</small>
                            </th>
                            <th class="text-center">Gaji Bersih</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrollBulanan as $i => $payroll)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-weight-bold">{{ $payroll->employee->name }}</td>
                                <td class="text-center">Rp {{ number_format($payroll->basic_salary) }}</td>
                                <td class="text-center">Rp {{ number_format($payroll->overtime_total) }}</td>
                                <td class="text-center">Rp {{ number_format($payroll->bonus_total) }}</td>
                                <td class="text-center font-weight-bold">Rp {{ number_format($payroll->gross_salary) }}
                                </td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format(($payroll->basic_salary * $rates['bpjs_kesehatan']) / 100) }}
                                </td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format(($payroll->basic_salary * $rates['bpjs_ketenagakerjaan']) / 100) }}
                                </td>
                                <td class="text-center text-danger">
                                    Rp {{ number_format(($payroll->basic_salary * $rates['management_fee']) / 100) }}
                                </td>
                                <td class="text-center font-weight-bold text-success">
                                    Rp {{ number_format($payroll->net_salary) }}
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge {{ $payroll->status === 'FINAL' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $payroll->status }}
                                    </span>
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('admin.payroll.bulanan.print', $payroll->id) }}"
                                        class="btn btn-primary btn-sm" target="_blank">
                                        Show
                                    </a>

                                    @if (auth()->user()->role->name === 'SUPER_ADMIN')
                                        @if ($payroll->status === 'DRAFT')
                                            <form action="{{ route('admin.payroll.bulanan.finalize', $payroll->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-success btn-sm"
                                                    onclick="return confirm('Finalisasi payroll {{ $payroll->employee->name }}?')">
                                                    <i class="fas fa-check-circle"></i> Finalisasi
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">
                                    Belum ada data payroll untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="2">Grand Total</td>
                            <td class="text-center">Rp {{ number_format($grandTotal['basic_salary']) }}</td>
                            <td class="text-center">Rp {{ number_format($grandTotal['overtime_total']) }}</td>
                            <td class="text-center">Rp {{ number_format($grandTotal['bonus_total']) }}</td>
                            <td class="text-center">Rp {{ number_format($grandTotal['gross_salary']) }}</td>
                            {{-- <td class="text-center text-danger">Rp {{ number_format($grandTotal['deduction_total']) }} --}}
                            </td>
                            <td class="text-center text-danger">
                                Rp {{ number_format(($grandTotal['basic_salary'] * $rates['bpjs_kesehatan']) / 100) }}
                            </td>
                            <td class="text-center text-danger">
                                Rp
                                {{ number_format(($grandTotal['basic_salary'] * $rates['bpjs_ketenagakerjaan']) / 100) }}
                            </td>
                            <td class="text-center text-danger">
                                Rp {{ number_format(($grandTotal['basic_salary'] * $rates['management_fee']) / 100) }}
                            </td>
                            <td class="text-center text-success">Rp {{ number_format($grandTotal['net_salary']) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="card-footer text-right">
                    {{ $payrollBulanan->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
