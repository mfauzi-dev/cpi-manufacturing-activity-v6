@extends('layouts.master')

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
                        {{ $payrolls->count() }} orang
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
            <h4>
                {{ $periodLabel }}
            </h4>
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

                    <a href="{{ route('general-manager.penggajian-borongan.index') }}" class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </form>

        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">

        <div class="card-header">
            <h4>Data Penggajian Borongan</h4>
        </div>

        <div class="card-body">

            {{-- EXPORT PDF --}}
            <div class="mb-3">
                <a href="{{ route('general-manager.penggajian-borongan.export-pdf', [
                    'month' => $month,
                    'year' => $year,
                    'department_id' => $departmentId,
                ]) }}"
                    class="btn btn-danger" target="_blank">

                    <i class="fas fa-file-pdf"></i>
                    PDF

                </a>
            </div>

            <div class="table-responsive">

                <table class="table table-striped mb-0">

                    <thead>
                        <tr>
                            <th width="40">No.</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th class="text-center">Total Kg</th>
                            <th class="text-center">Total Upah</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($payrolls as $i => $payroll)
                            <tr>

                                <td>
                                    {{ $payrolls->firstItem() + $i }}
                                </td>

                                <td>
                                    {{ $payroll->employee->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $payroll->employee->department->name ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($payroll->total_kg, 2, ',', '.') }} Kg
                                </td>

                                <td class="text-center font-weight-bold text-success">
                                    Rp {{ number_format($payroll->total_upah, 0, ',', '.') }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
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
