@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Attendance</h1>
    </div>

    <div class="alert alert-info">
        Department : <strong>Semua Department</strong>
    </div>

    <div class="section-body">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('manager.attendance.index') }}">
                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
                        </div>

                        <div class="col-md-3 mb-2">
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

                        <div class="col-md-3 mb-2">
                            <select name="cost_center_id" id="cost_center_id" class="form-control">
                                <option value="">Semua Cost Center</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="ps_group_id" id="ps_group_id" class="form-control">
                                <option value="">Semua PS Group</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="cuti" {{ request('status') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                <option value="alfa" {{ request('status') == 'alfa' ? 'selected' : '' }}>Alpa</option>
                                <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
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

                        <div class="col-md-3 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                value="{{ request('search') }}">
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('manager.attendance.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th>OS</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Input By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php $attendance = $employee->attendances->first(); @endphp
                            <tr>
                                <td>{{ $employee->nik }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->department?->name ?? '-' }}</td>
                                <td>{{ $employee->outsourcing?->name ?? '-' }}</td>
                                <td>{{ $employee->psGroup->name ?? '-' }}</td>
                                <td>
                                    @if (!$attendance)
                                        -
                                    @elseif ($attendance->status == 'hadir')
                                        <span class="badge badge-success">Hadir</span>
                                    @elseif ($attendance->status == 'cuti')
                                        <span class="badge badge-primary">Cuti</span>
                                    @elseif ($attendance->status == 'izin')
                                        <span class="badge badge-warning">Izin</span>
                                    @elseif ($attendance->status == 'sakit')
                                        <span class="badge badge-info">Sakit</span>
                                    @elseif ($attendance->status == 'alfa')
                                        <span class="badge badge-danger">Alpa</span>
                                    @endif
                                </td>
                                <td>{{ $attendance?->keterangan_izin ?? '-' }}</td>
                                <td>{{ $attendance?->inputBy?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data attendance</td>
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
                $('#ps_group_id').html('<option value="">Semua PS Group</option>');
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

                $('#ps_group_id').html('<option value="">Semua PS Group</option>');
                return;

            }

            $.get('/attendance/ps-groups/' + costCenterId, function(res) {

                let html = '<option value="">Semua PS Group</option>';

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
