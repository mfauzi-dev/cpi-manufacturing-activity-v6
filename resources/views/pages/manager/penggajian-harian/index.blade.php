@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Penggajian Harian</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                Penggajian Harian
            </div>
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="row">

        {{-- TOTAL KARYAWAN --}}
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
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

        {{-- TOTAL HARI KERJA --}}
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-info">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Hari Kerja</h4>
                    </div>
                    <div class="card-body">
                        {{ number_format($grandTotalWorkDays, 0, ',', '.') }} Hari
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL UPAH HARIAN --}}
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Upah</h4>
                    </div>
                    <div class="card-body">
                        Rp {{ number_format($grandTotalUpahHarian, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAND TOTAL --}}
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Grand Total Diterima</h4>
                    </div>
                    <div class="card-body">
                        Rp {{ number_format($grandTotalNetSalary, 0, ',', '.') }}
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Outsourcing</label>

                            <select name="outsourcing_id" class="form-control">
                                <option value="">
                                    Semua Outsourcing
                                </option>

                                @foreach ($outsourcings as $outsourcing)
                                    <option value="{{ $outsourcing->id }}"
                                        {{ (string) $outsourcingId === (string) $outsourcing->id ? 'selected' : '' }}>
                                        {{ $outsourcing->name }}
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

                    <a href="{{ route('manager.penggajian-harian.index') }}" class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </form>
        </div>
    </div>

    {{-- DATA --}}
    <div class="card">

        <div class="card-header">
            <h4>Data Penggajian Harian</h4>
        </div>

        <div class="card-body">

            {{-- EXPORT --}}
            <div class="mb-3">

                <a href="{{ route('manager.penggajian-harian.export-excel', [
                    'month' => $month,
                    'year' => $year,
                    'outsourcing_id' => $outsourcingId,
                ]) }}"
                    class="btn btn-success">

                    <i class="fas fa-file-excel"></i>
                    Excel

                </a>

                <a href="{{ route('manager.penggajian-harian.export-pdf', [
                    'month' => $month,
                    'year' => $year,
                    'outsourcing_id' => $outsourcingId,
                ]) }}"
                    class="btn btn-danger" target="_blank">

                    <i class="fas fa-file-pdf"></i>
                    PDF

                </a>

            </div>

            <div class="table-responsive">

                <table class="table table-striped table-bordered mb-0">

                    <thead>
                        <tr>

                            <th class="text-center" width="50">
                                No.
                            </th>

                            <th>
                                No. KTP
                            </th>

                            <th>
                                NIK
                            </th>

                            <th>
                                Nama
                            </th>

                            <th class="text-center">
                                UMP
                            </th>

                            <th class="text-center">
                                Standar Hari Kerja
                            </th>

                            <th class="text-center">
                                Total Hari Kerja
                            </th>

                            <th class="text-right">
                                Upah Harian
                            </th>

                            <th class="text-right">
                                Jamsostek (4.89%)
                            </th>

                            <th class="text-right">
                                BPJS Kesehatan (4%)
                            </th>

                            <th class="text-right">
                                BPJS Pensiun (2%)
                            </th>

                            <th class="text-right">
                                Management Fee (175000/25)
                            </th>

                            <th class="text-right">
                                Grand Total Upah Diterima
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($payrolls as $i => $payroll)
                            <tr>

                                {{-- NO --}}
                                <td class="text-center">
                                    {{ $payrolls->firstItem() + $i }}
                                </td>

                                {{-- KTP --}}
                                <td>
                                    {{ $payroll->employee->ktp_number ?? '-' }}
                                </td>

                                {{-- NIK --}}
                                <td>
                                    {{ $payroll->employee->nik ?? '-' }}
                                </td>

                                {{-- NAMA --}}
                                <td>
                                    {{ $payroll->employee->name ?? '-' }}
                                </td>

                                {{-- UMP --}}
                                <td class="text-right">
                                    Rp {{ number_format($payroll->ump_used ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- STANDAR HARI KERJA --}}
                                <td class="text-center">
                                    {{ $payroll->hari_kerja_standar_used ?? 0 }}
                                </td>

                                {{-- TOTAL HARI KERJA --}}
                                <td class="text-center">
                                    {{ $payroll->work_days ?? 0 }}
                                </td>

                                {{-- UPAH HARIAN --}}
                                <td class="text-right">
                                    Rp {{ number_format($payroll->upah_harian ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- JAMSOSTEK --}}
                                <td class="text-right">
                                    Rp {{ number_format($payroll->jamsostek ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- BPJS KESEHATAN --}}
                                <td class="text-right">
                                    Rp {{ number_format($payroll->bpjs_kesehatan ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- BPJS PENSIUN --}}
                                <td class="text-right">
                                    Rp {{ number_format($payroll->bpjs_pensiun ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- MANAGEMENT FEE --}}
                                <td class="text-right">
                                    Rp {{ number_format($payroll->managemen_fee ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- GRAND TOTAL --}}
                                <td class="text-right font-weight-bold text-success">
                                    Rp {{ number_format($payroll->grand_total_upah ?? 0, 0, ',', '.') }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="13" class="text-center py-4 text-muted">

                                    Belum ada penggajian harian untuk periode ini.

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
