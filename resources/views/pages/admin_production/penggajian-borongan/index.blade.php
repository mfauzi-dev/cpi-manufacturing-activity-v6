@extends('layouts.master')

@push('addon-style')
    <style>
        .payroll-table {
            border-collapse: collapse;
        }

        .payroll-table th,
        .payroll-table td {
            border: 1px solid #dee2e6 !important;
        }

        .payroll-table thead th {
            border: 1px solid #dee2e6 !important;
            text-align: center;
            vertical-align: middle;
            background: #f8f9fa;
        }

        .cost-center-name {
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <div class="section-header">
        <h1>Penggajian Borongan</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                Penggajian Borongan
            </div>
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="row">

        {{-- TOTAL KARYAWAN --}}
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
                        {{ $payrolls->total() }} orang
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL KG --}}
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-info">
                    <i class="fas fa-weight-hanging"></i>
                </div>

                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Produksi</h4>
                    </div>

                    <div class="card-body">
                        {{ number_format($grandTotalKg, 2, ',', '.') }} Kg
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL UPAH --}}
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-wallet"></i>
                </div>

                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Upah</h4>
                    </div>

                    <div class="card-body">
                        Rp {{ number_format($grandTotalUpah, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- FILTER --}}
    <div class="card">

        <div class="card-header">
            <h4>{{ $periodLabel }}</h4>
        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    {{-- MONTH --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Bulan</label>

                            <select name="month" class="form-control">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- YEAR --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun</label>

                            <select name="year" class="form-control">
                                @foreach (range(now()->year, now()->year - 3) as $y)
                                    <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- OUTSOURCING --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Outsourcing</label>

                            <select name="outsourcing_id" class="form-control">
                                <option value="">Semua Outsourcing</option>

                                @foreach ($outsourcings as $outsourcing)
                                    <option value="{{ $outsourcing->id }}"
                                        {{ (string) $outsourcingId === (string) $outsourcing->id ? 'selected' : '' }}>
                                        {{ $outsourcing->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- COST CENTER --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost Center</label>

                            <select name="cost_center_id" class="form-control">
                                <option value="">Semua Cost Center</option>

                                @foreach ($costCenters as $costCenter)
                                    <option value="{{ $costCenter->id }}"
                                        {{ (string) $costCenterId === (string) $costCenter->id ? 'selected' : '' }}>
                                        {{ $costCenter->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Filter
                    </button>

                    <a href="{{ route('admin-production.penggajian-borongan.index') }}" class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </form>

        </div>

    </div>

    <div class="card">

        <div class="card-header">
            <h4>Data Penggajian Borongan</h4>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('admin-production.penggajian-borongan.export-excel', [
                    'month' => $month,
                    'year' => $year,
                    'outsourcing_id' => $outsourcingId,
                    'cost_center_id' => $costCenterId,
                ]) }}"
                    class="btn btn-success"> <i class="fas fa-file-excel"></i> Excel </a>


                <a href="{{ route('admin-production.penggajian-borongan.export-pdf', [
                    'month' => $month,
                    'year' => $year,
                    'outsourcing_id' => $outsourcingId,
                    'cost_center_id' => $costCenterId,
                ]) }}"
                    class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                </a>
            </div>

            <div class="table-responsive">

                <table class="table table-striped table-bordered payroll-table mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center" width="50">
                                No.
                            </th>

                            <th rowspan="2">
                                No. KTP
                            </th>

                            <th rowspan="2">
                                NIK
                            </th>

                            <th rowspan="2">
                                Nama
                            </th>

                            <th rowspan="2" class="text-center">
                                Hasil Proses (Kg)/Jam
                            </th>

                            <th rowspan="2" class="text-center">
                                Total Hari
                            </th>

                            <th colspan="{{ $costCenters->count() }}" class="text-center">
                                UPAH YANG DITERIMA
                            </th>

                            <th rowspan="2" class="text-center">
                                Total Upah yang Diterima
                            </th>

                            <th rowspan="2" class="text-center">
                                Jamsostek (4.89%)
                            </th>

                            <th rowspan="2" class="text-center">
                                BPJS Kesehatan (4%)
                            </th>

                            <th rowspan="2" class="text-center">
                                BPJS Pensiun (2%)
                            </th>

                            <th rowspan="2" class="text-center">
                                Managemen Fee
                                <br>
                                (175000/25)
                            </th>

                            <th rowspan="2" class="text-center">
                                Grand Total Upah Diterima
                            </th>
                        </tr>

                        <tr>
                            @foreach ($costCenters as $costCenter)
                                <th class="text-center">
                                    {{ $costCenter->code }}
                                    <br>
                                    <small class="cost-center-name">{{ $costCenter->name }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payrolls as $i => $payroll)
                            <tr>
                                <td class="text-center">
                                    {{ $payrolls->firstItem() + $i }}
                                </td>

                                <td>
                                    {{ $payroll->employee->ktp_number ?? '-' }}
                                </td>

                                <td>
                                    {{ $payroll->employee->nik ?? '-' }}
                                </td>

                                <td>
                                    {{ $payroll->employee->name ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($payroll->total_kg ?? 0, 2, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    {{ $payroll->total_hari_kerja ?? 0 }}
                                </td>

                                @foreach ($costCenters as $costCenter)
                                    <td class="text-right">
                                        @php
                                            $upahCostCenter = $payroll->cost_center_upah[$costCenter->id] ?? 0;
                                        @endphp

                                        @if ($upahCostCenter > 0)
                                            Rp {{ number_format($upahCostCenter, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach

                                <td class="text-right">
                                    Rp {{ number_format($payroll->total_upah ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format($payroll->jamsostek ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format($payroll->bpjs_kesehatan ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format($payroll->bpjs_pensiun ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format($payroll->managemen_fee ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right font-weight-bold text-success">
                                    Rp {{ number_format($payroll->grand_total_upah ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 13 + $costCenters->count() }}" class="text-center py-4 text-muted">
                                    Belum ada penggajian borongan untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

        <div class="card-footer text-right">

            {{ $payrolls->withQueryString()->links() }}

        </div>
    </div>
@endsection
