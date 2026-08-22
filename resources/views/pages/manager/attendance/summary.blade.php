@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Summary Attendance - All Department</h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('manager.attendance.summary') }}">

                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>
                                    Bulan
                                </label>
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

                        <div class="col-md-3">
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


                        <div class="col-md-3">
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

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">Semua Department</option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cost Center</label>
                                <select name="cost_center_id" id="cost_center_id" class="form-control">
                                    <option value="">Semua Cost Center</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Group</label>
                                <select name="ps_group_id" id="ps_group_id" class="form-control">
                                    <option value="">Semua Group</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3">
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

                        <a href="{{ route('manager.attendance.summary') }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        {{-- TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">
                <div class="mb-2">
                    <a href="{{ route('manager.attendance.summary.export-excel', request()->query()) }}"
                        class="btn btn-success">
                        <i class="fas fa-file-excel"></i>
                        Excel
                    </a>

                    <a href="{{ route('manager.attendance.summary.export-pdf', request()->query()) }}"
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
                                    <a href="{{ route('manager.attendance.summary.detail', array_merge(request()->query(), ['employee' => $employee->id])) }}"
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

            loadCostCenters();

            $('#department_id').change(function() {

                loadCostCenters();

            });

            $('#cost_center_id').change(function() {

                loadPsGroups();

            });

        });

        function loadCostCenters() {

            let departmentId = $('#department_id').val();

            if (departmentId == '') {

                $('#cost_center_id').html('<option value="">Semua Cost Center</option>');
                $('#ps_group_id').html('<option value="">Semua Group</option>');
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

                $('#ps_group_id').html('<option value="">Semua Group</option>');
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
