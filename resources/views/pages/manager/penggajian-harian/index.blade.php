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

                    {{-- BULAN --}}
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

                    {{-- TAHUN --}}
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

                    {{-- DEPARTMENT --}}
                    <div class="col-md-3">
                        <div class="form-group">

                            <label>Department</label>

                            @if ($isHrDepartment)
                                <select name="department_id" id="department_id" class="form-control">

                                    <option value="">
                                        Semua Department
                                    </option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ (string) $departmentId === (string) $department->id ? 'selected' : '' }}>

                                            {{ $department->name }}

                                        </option>
                                    @endforeach

                                </select>
                            @else
                                <input type="text" class="form-control"
                                    value="{{ auth()->user()->department->name ?? '-' }}" readonly>

                                <input type="hidden" name="department_id" id="department_id" value="{{ $departmentId }}">
                            @endif

                        </div>
                    </div>

                    {{-- COST CENTER --}}
                    <div class="col-md-3">
                        <div class="form-group">

                            <label>Cost Center</label>

                            <select name="cost_center_id" id="cost_center_id" class="form-control">

                                <option value="">
                                    Loading...
                                </option>

                            </select>

                        </div>
                    </div>

                    {{-- OUTSOURCING --}}
                    <div class="col-md-3">
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

                    {{-- SEARCH --}}
                    <div class="col-md-6">
                        <div class="form-group">

                            <label>
                                Cari NIK / Nama
                            </label>

                            <input type="text" name="search" class="form-control" value="{{ $search }}"
                                placeholder="Masukkan NIK atau nama karyawan">

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
                    'department_id' => $departmentId,
                    'cost_center_id' => $costCenterId,
                    'outsourcing_id' => $outsourcingId,
                    'search' => $search,
                ]) }}"
                    class="btn btn-success">

                    <i class="fas fa-file-excel"></i>
                    Excel

                </a>

                <a href="{{ route('manager.penggajian-harian.export-pdf', [
                    'month' => $month,
                    'year' => $year,
                    'department_id' => $departmentId,
                    'cost_center_id' => $costCenterId,
                    'outsourcing_id' => $outsourcingId,
                    'search' => $search,
                ]) }}"
                    class="btn btn-danger" target="_blank">

                    <i class="fas fa-file-pdf"></i>
                    PDF

                </a>

            </div>

            {{-- TABLE --}}
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
                                Total Overtime
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

                                <td class="text-right">
                                    Rp {{ number_format($payroll->ump_used ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    {{ $payroll->hari_kerja_standar_used ?? 0 }}
                                </td>

                                <td class="text-center">
                                    {{ $payroll->work_days ?? 0 }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format($payroll->upah_harian ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format($payroll->overtime_total ?? 0, 0, ',', '.') }}
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

                                <td colspan="14" class="text-center py-4 text-muted">

                                    Belum ada penggajian harian untuk periode ini.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- PAGINATION --}}
        <div class="card-footer text-right">

            {{ $payrolls->withQueryString()->links() }}

        </div>

    </div>

@endsection


@push('scripts')
    <script>
        $(document).ready(function() {

            let departmentId = $('#department_id').val();
            let selectedCostCenter = "{{ $costCenterId ?? '' }}";
            let costCenter = $('#cost_center_id');

            function loadCostCenters(departmentId, selectedId = '') {

                costCenter.html(
                    '<option value="">Loading...</option>'
                );

                if (!departmentId) {

                    costCenter.html(
                        '<option value="">Semua Cost Center</option>'
                    );

                    return;
                }

                $.ajax({

                    url: "{{ url('/penggajian-harian/cost-centers') }}/" + departmentId,

                    type: "GET",

                    dataType: "json",

                    success: function(data) {

                        costCenter.empty();

                        costCenter.append(
                            '<option value="">Semua Cost Center</option>'
                        );

                        $.each(data, function(key, value) {

                            let selected =
                                String(value.id) === String(selectedId) ?
                                'selected' :
                                '';

                            costCenter.append(
                                '<option value="' + value.id + '" ' +
                                selected + '>' +
                                value.name +
                                '</option>'
                            );

                        });

                    },

                    error: function() {

                        costCenter.empty();

                        costCenter.append(
                            '<option value="">Gagal memuat Cost Center</option>'
                        );

                    }

                });
            }

            loadCostCenters(departmentId, selectedCostCenter);

            @if ($isHrDepartment)

                $('#department_id').on('change', function() {

                    let departmentId = $(this).val();

                    loadCostCenters(departmentId);

                });
            @endif

        });
    </script>
@endpush
