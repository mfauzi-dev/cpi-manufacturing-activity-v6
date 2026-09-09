@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Attendance</h1>
    </div>

    <div class="alert alert-info">
        Department :
        <strong>{{ auth()->user()->department->name }}</strong>
    </div>

    <div class="section-body">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('admin-production.attendance.index') }}">

                    <div class="row">

                        @if ($isGeneralAffair)
                            <div class="col-md-3 mb-2">
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department_id" id="department_id" class="form-control">
                                        <option value="">Semua Department</option>

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
                                <label>Tanggal</label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ request('date', $date) }}">
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">

                                    <option value="">Semua Status</option>

                                    <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>
                                        Hadir
                                    </option>

                                    <option value="cuti" {{ request('status') == 'cuti' ? 'selected' : '' }}>
                                        Cuti
                                    </option>

                                    <option value="alfa" {{ request('status') == 'alfa' ? 'selected' : '' }}>
                                        Alpa
                                    </option>

                                    <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>
                                        Izin
                                    </option>

                                    <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>
                                        Sakit
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Status Karyawan</label>
                                <select name="employee_status" class="form-control">

                                    <option value="">Semua Status Karyawan</option>

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
                                        {{ request('employee_status') == 'harian' ? 'selected' : '' }}>
                                        Harian Kontrak
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Outsourcing</label>
                                <select name="outsourcing_id" class="form-control">

                                    <option value="">Semua OS</option>

                                    @foreach ($outsourcings as $os)
                                        <option value="{{ $os->id }}"
                                            {{ request('outsourcing_id') == $os->id ? 'selected' : '' }}>
                                            {{ $os->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Cost Center</label>
                                <select name="cost_center_id" id="cost_center_id" class="form-control">

                                    <option value="">Semua Cost Center</option>

                                    @foreach ($costCenters as $cost_center)
                                        <option value="{{ $cost_center->id }}"
                                            {{ request('cost_center_id') == $cost_center->id ? 'selected' : '' }}>
                                            {{ $cost_center->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Group</label>
                                <select name="ps_group_id" id="ps_group_id" class="form-control">
                                    <option value="">Semua Group</option>
                                </select>
                            </div>
                        </div>

                        @if (strtolower(auth()->user()->department->name) === 'further processing')
                            <div class="col-md-3 mb-2">
                                <div class="form-group">
                                    <label>Line</label>
                                    <select name="line_id" class="form-control">
                                        <option value="">Semua Line</option>

                                        @foreach ($lineList as $line)
                                            <option value="{{ $line->id }}"
                                                {{ request('line_id') == $line->id ? 'selected' : '' }}>
                                                {{ $line->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        {{-- SEARCH --}}
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

                        <a href="{{ route('admin-production.attendance.index') }}" class="btn btn-secondary">
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


        {{-- TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            @if ($isGeneralAffair)
                                <th>Department</th>
                            @endif
                            <th>OS</th>
                            <th>Group</th>
                            @if (strtolower(auth()->user()->department->name) === 'further processing')
                                <th>Line</th>
                            @endif
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Input By</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            @php
                                $attendance = $employee->attendances->first();
                            @endphp

                            <tr>

                                <td>{{ $employee->nik }}</td>

                                <td>{{ $employee->name }}</td>

                                @if ($isGeneralAffair)
                                    <td>
                                        {{ $employee->department?->name ?? '-' }}
                                    </td>
                                @endif

                                <td>
                                    {{ $employee->outsourcing?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->psGroup->name ?? '-' }}
                                </td>

                                @if (strtolower(auth()->user()->department->name) === 'further processing')
                                    <td>
                                        {{ $attendance?->line?->name ?? '-' }}
                                    </td>
                                @endif

                                <td>

                                    @if (!$attendance)
                                        -
                                    @elseif ($attendance->status == 'hadir')
                                        <span class="badge badge-success">
                                            Hadir
                                        </span>
                                    @elseif ($attendance->status == 'cuti')
                                        <span class="badge badge-primary">
                                            Cuti
                                        </span>
                                    @elseif ($attendance->status == 'izin')
                                        <span class="badge badge-warning">
                                            Izin
                                        </span>
                                    @elseif ($attendance->status == 'sakit')
                                        <span class="badge badge-info">
                                            Sakit
                                        </span>
                                    @elseif ($attendance->status == 'alfa')
                                        <span class="badge badge-danger">
                                            Alfa
                                        </span>
                                    @endif

                                </td>

                                <td>
                                    {{ $attendance?->keterangan_izin ?? '-' }}
                                </td>

                                <td>
                                    {{ $attendance?->inputBy?->name ?? '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="{{ strtolower(auth()->user()->department->name) === 'further processing' ? 8 : 7 }}"
                                    class="text-center">
                                    Tidak ada data attendance
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
        $(function() {

            loadPsGroups();

            $('#cost_center_id').change(function() {
                loadPsGroups();
            });

            $('#department_id').change(function() {
                loadCostCenters();
            });

        });

        function loadCostCenters() {

            let departmentId = $('#department_id').val();

            if (departmentId == '') {

                $('#cost_center_id').html(
                    '<option value="">Semua Cost Center</option>'
                );

                loadPsGroups();

                return;
            }

            $.get('/attendance/cost-centers/' + departmentId, function(res) {

                let html = '<option value="">Semua Cost Center</option>';

                $.each(res, function(i, item) {

                    html += `
                <option value="${item.id}"
                    ${item.id == "{{ request('cost_center_id') }}" ? 'selected' : ''}>
                    ${item.name}
                </option>
            `;

                });

                $('#cost_center_id').html(html);

                loadPsGroups();

            });

        }

        function loadPsGroups() {

            let costCenterId = $('#cost_center_id').val();

            if (costCenterId == '') {

                $('#ps_group_id').html(
                    '<option value="">Semua Group</option>'
                );

                return;
            }

            $.get('/attendance/ps-groups/' + costCenterId, function(res) {

                let html = '<option value="">Semua Group</option>';

                $.each(res, function(i, item) {

                    html += `
                <option value="${item.id}"
                    ${item.id == "{{ request('ps_group_id') }}" ? 'selected' : ''}>
                    ${item.name}
                </option>
            `;

                });

                $('#ps_group_id').html(html);

            });

        }
    </script>
@endpush
