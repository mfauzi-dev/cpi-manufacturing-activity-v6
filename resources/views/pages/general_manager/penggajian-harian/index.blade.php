@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Payroll Upah Harian</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                Payroll Upah Harian
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
                        {{ $payrolls->count() }} orang
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL HARI KERJA --}}
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-info">
                    <i class="fas fa-calendar-check"></i>
                </div>

                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Hari Kerja</h4>
                    </div>

                    <div class="card-body">
                        {{ number_format($grandTotalWorkDays) }} hari
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL GAJI BERSIH --}}
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
                        Rp {{ number_format($grandTotalNetSalary) }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card">

        <div class="card-header">
            <h4>
                {{ $periodLabel }}
                · UMP Rp {{ number_format($ump) }}
                / {{ $hariKerjaStandar }} hari
            </h4>
        </div>

        <div class="card-body">

            {{-- FILTER --}}
            <form method="GET" class="p-3 border-bottom">

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

                    {{-- DEPARTMENT --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Department</label>

                            <select name="department_id" class="form-control">

                                <option value="">
                                    Semua Department
                                </option>

                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $departmentId == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
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

                    <a href="{{ route('general-manager.penggajian-harian.index') }}" class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </form>
        </div>
    </div>

    <div class="card">

        <div class="table-responsive">

            <div class="card-body">

                <div class="mb-2">
                    <a href="{{ route('general-manager.penggajian-harian.export-pdf', [
                        'month' => $month,
                        'year' => $year,
                        'department_id' => $departmentId,
                    ]) }}"
                        class="btn btn-danger" target="_blank"> <i class="fas fa-file-pdf"></i> PDF </a>
                </div>
                <table class="table table-striped mb-0">

                    <thead>
                        <tr>
                            <th width="40">No.</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th class="text-center">Hari Kerja</th>
                            <th class="text-center">Upah Harian</th>
                            <th class="text-center">Gaji Bersih</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($payrolls as $i => $payroll)
                            <tr>

                                <td>
                                    {{ $payrolls->firstItem() + $i }}
                                </td>

                                <td>
                                    {{ $payroll->employee->name }}
                                </td>

                                <td>
                                    {{ $payroll->employee->department->name ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $payroll->work_days }} hari
                                </td>

                                <td class="text-center">
                                    Rp {{ number_format($payroll->upah_harian) }}
                                </td>

                                <td class="text-center font-weight-bold text-success">
                                    Rp {{ number_format($payroll->net_salary) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada karyawan harian untuk periode ini.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="card-footer text-right">
                {{ $payrolls->withQueryString()->links() }}
            </div>

        </div>

    </div>
@endsection
