@extends('layouts.master')

@section('content')

    <div class="section-header">
        <h1>Summary Attendance</h1>
    </div>

    <div class="section-body">

        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('admin-production.attendance.summary') }}">

                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Bulan</label>

                                <select name="month" class="form-control">

                                    @php
                                        $bulanList = [
                                            1 => 'Januari',
                                            2 => 'Februari',
                                            3 => 'Maret',
                                            4 => 'April',
                                            5 => 'Mei',
                                            6 => 'Juni',
                                            7 => 'Juli',
                                            8 => 'Agustus',
                                            9 => 'September',
                                            10 => 'Oktober',
                                            11 => 'November',
                                            12 => 'Desember',
                                        ];
                                    @endphp

                                    @foreach ($bulanList as $value => $label)
                                        <option value="{{ $value }}" {{ $monthNum == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Tahun</label>

                                <select name="year" class="form-control">

                                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor

                                </select>
                            </div>
                        </div>

                        @if ($isGeneralAffair)
                            <div class="col-md-3 mb-2">
                                <div class="form-group">
                                    <label>Department</label>

                                    <select name="department_id" id="department_id" class="form-control">

                                        <option value="">
                                            Semua Department
                                        </option>

                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ (string) request('department_id') === (string) $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Cost Center</label>

                                <select name="cost_center_id" id="cost_center_id" class="form-control">

                                    <option value="">
                                        Semua Cost Center
                                    </option>

                                    @if (!$isGeneralAffair)
                                        @foreach ($costCenters as $costCenter)
                                            <option value="{{ $costCenter->id }}"
                                                {{ request('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                                {{ $costCenter->name }}
                                            </option>
                                        @endforeach
                                    @endif

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Group</label>

                                <select name="ps_group_id" id="ps_group_id" class="form-control">

                                    <option value="">
                                        Semua Group
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Status Karyawan</label>

                                <select name="employee_status" class="form-control">

                                    <option value="">
                                        Semua Status Karyawan
                                    </option>

                                    <option value="cpi" {{ request('employee_status') == 'cpi' ? 'selected' : '' }}>
                                        CPI
                                    </option>

                                    <option value="borongan"
                                        {{ request('employee_status') == 'borongan' ? 'selected' : '' }}>
                                        Borongan
                                    </option>

                                    <option value="harian" {{ request('employee_status') == 'harian' ? 'selected' : '' }}>
                                        Harian
                                    </option>

                                    <option value="harian_kontrak"
                                        {{ request('employee_status') == 'harian_kontrak' ? 'selected' : '' }}>
                                        Harian Kontrak
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Outsourcing</label>

                                <select name="outsourcing_id" class="form-control">

                                    <option value="">
                                        Semua OS
                                    </option>

                                    @foreach ($outsourcings as $os)
                                        <option value="{{ $os->id }}"
                                            {{ request('outsourcing_id') == $os->id ? 'selected' : '' }}>
                                            {{ $os->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        @if ($isGeneralAffair)
                            <div class="col-md-3 mb-2">
                                <div class="form-group">
                                    <label>Line</label>

                                    <select name="line_id" id="line_id" class="form-control">

                                        <option value="">
                                            Semua Line
                                        </option>

                                        @foreach ($lineList as $line)
                                            <option value="{{ $line->id }}"
                                                {{ request('line_id') == $line->id ? 'selected' : '' }}>
                                                {{ $line->code ? $line->code . ' - ' : '' }}{{ $line->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        @elseif (strtolower(auth()->user()->department->name) === 'further processing')
                            <div class="col-md-3 mb-2">
                                <div class="form-group">
                                    <label>Line</label>

                                    <select name="line_id" class="form-control">

                                        <option value="">
                                            Semua Line
                                        </option>

                                        @foreach ($lineList as $line)
                                            <option value="{{ $line->id }}"
                                                {{ request('line_id') == $line->id ? 'selected' : '' }}>
                                                {{ $line->code ? $line->code . ' - ' : '' }}{{ $line->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Nama Karyawan</label>

                                <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                    </div>

                    <div>

                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('admin-production.attendance.summary') }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        <div class="row mb-2">

            <div class="col-md-3">

                <div class="card card-primary">

                    <div class="card-body">

                        <div class="text-muted">
                            Total Karyawan
                        </div>

                        <h3 class="mb-0">
                            {{ $totalEmployee }} Orang
                        </h3>

                    </div>

                </div>

            </div>

        </div>

        <div class="card">

            <div class="card-body table-responsive">

                <div class="mb-2">

                    <a href="{{ route('admin-production.attendance.summary.export-excel', request()->query()) }}"
                        class="btn btn-success">

                        <i class="fas fa-file-excel"></i>
                        Excel

                    </a>

                    <a href="{{ route('admin-production.attendance.summary.export-pdf', request()->query()) }}"
                        class="btn btn-danger" target="_blank">

                        <i class="fas fa-file-pdf"></i>
                        PDF

                    </a>

                </div>

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th>NIK</th>

                            <th>Nama</th>

                            <th>Department</th>

                            <th>OS</th>

                            <th>Group</th>

                            @if ($isGeneralAffair || strtolower(auth()->user()->department->name) === 'further processing')
                                <th>Line</th>
                            @endif

                            <th class="text-center">
                                Hadir
                            </th>

                            <th class="text-center">
                                Izin
                            </th>

                            <th class="text-center">
                                Sakit
                            </th>

                            <th class="text-center">
                                Cuti
                            </th>

                            <th class="text-center">
                                Alpa
                            </th>

                            <th></th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>

                                <td>
                                    {{ $employee->nik }}
                                </td>

                                <td>
                                    {{ $employee->name }}
                                </td>

                                <td>
                                    {{ $employee->department?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->outsourcing?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->psGroup?->name ?? '-' }}
                                </td>

                                @if ($isGeneralAffair || strtolower(auth()->user()->department->name) === 'further processing')
                                    <td>
                                        {{ $employee->line?->name ?? '-' }}
                                    </td>
                                @endif

                                <td class="text-center">
                                    {{ $employee->total_hadir }}
                                </td>

                                <td class="text-center">
                                    {{ $employee->total_izin }}
                                </td>

                                <td class="text-center">
                                    {{ $employee->total_sakit }}
                                </td>

                                <td class="text-center">
                                    {{ $employee->total_cuti }}
                                </td>

                                <td class="text-center">
                                    {{ $employee->total_alfa }}
                                </td>

                                <td class="text-right">

                                    <a href="{{ route('admin-production.attendance.summary.detail', array_merge(request()->query(), ['employee' => $employee->id])) }}"
                                        class="btn btn-sm btn-info">

                                        Detail

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="{{ $isGeneralAffair || strtolower(auth()->user()->department->name) === 'further processing' ? 12 : 11 }}"
                                    class="text-center">

                                    Tidak ada data employee

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="card-footer text-right">

                {{ $employees->withQueryString()->links() }}

            </div>

        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const isGeneralAffair = @json($isGeneralAffair);

        const managerDepartmentId = "{{ auth()->user()->department_id }}";

        const selectedCostCenterId = "{{ request('cost_center_id') }}";

        const selectedPsGroupId = "{{ request('ps_group_id') }}";

        $(function() {

            if (isGeneralAffair) {

                loadCostCenters();

                $('#department_id').change(function() {
                    loadCostCenters();
                });

            } else {

                loadPsGroups();

            }

            $('#cost_center_id').change(function() {
                loadPsGroups();
            });

        });

        function loadCostCenters() {

            let departmentId;

            if (isGeneralAffair) {
                departmentId = $('#department_id').val();
            } else {
                departmentId = managerDepartmentId;
            }

            if (!departmentId) {

                $('#cost_center_id').html(
                    '<option value="">Semua Cost Center</option>'
                );

                $('#ps_group_id').html(
                    '<option value="">Semua Group</option>'
                );

                return;
            }

            $.get(
                '/attendance/cost-centers/' + departmentId,
                function(res) {

                    let html =
                        '<option value="">Semua Cost Center</option>';

                    $.each(res, function(i, item) {

                        html += `
                        <option value="${item.id}"
                            ${item.id == selectedCostCenterId ? 'selected' : ''}>
                            ${item.name}
                        </option>
                    `;

                    });

                    $('#cost_center_id').html(html);

                    loadPsGroups();

                }
            );

        }

        function loadPsGroups() {

            let costCenterId =
                $('#cost_center_id').val();

            if (!costCenterId) {

                $('#ps_group_id').html(
                    '<option value="">Semua Group</option>'
                );

                return;
            }

            $.get(
                '/attendance/ps-groups/' + costCenterId,
                function(res) {

                    let html =
                        '<option value="">Semua Group</option>';

                    $.each(res, function(i, item) {

                        html += `
                        <option value="${item.id}"
                            ${item.id == selectedPsGroupId ? 'selected' : ''}>
                            ${item.name}
                        </option>
                    `;

                    });

                    $('#ps_group_id').html(html);

                }
            );

        }
    </script>
@endpush
