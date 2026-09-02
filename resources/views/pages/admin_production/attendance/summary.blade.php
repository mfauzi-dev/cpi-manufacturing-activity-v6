@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Summary Attendance</h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
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

        {{-- TABLE --}}
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
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Cuti</th>
                            <th class="text-center">Alpa</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>

                                <td>{{ $employee->nik }}</td>

                                <td>{{ $employee->name }}</td>

                                <td>{{ $employee->department->name ?? '-' }}</td>

                                <td>{{ $employee->outsourcing->name ?? '-' }}</td>

                                <td>{{ $employee->psGroup->name ?? '-' }}</td>

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
                                <td colspan="11" class="text-center">
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
        $(function() {

            loadPsGroups();

            $('#cost_center_id').change(function() {
                loadPsGroups();
            });

        });

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
