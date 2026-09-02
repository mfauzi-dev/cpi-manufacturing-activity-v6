@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Attendance</h1>
    </div>

    <div class="alert alert-info">
        Department : <strong>{{ $managerDepartment->name }}</strong>
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
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ request('date', $date) }}">

                            </div>
                        </div>

                        {{-- Dropdown Department dihapus. Manager sudah di-lock
                             ke department-nya sendiri (lihat badge di atas). --}}

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Cost Center</label>
                                <select name="cost_center_id" id="cost_center_id" class="form-control">
                                    <option value="">Semua Cost Center</option>
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

                        @if (strtolower($managerDepartment->name) === 'further processing')
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
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir
                                    </option>
                                    <option value="cuti" {{ request('status') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                    <option value="alfa" {{ request('status') == 'alfa' ? 'selected' : '' }}>Alpa
                                    </option>
                                    <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin
                                    </option>
                                    <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit
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
                                <label>Cari Karyawan</label>
                                <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                    </div>

                    <div>
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
                            @if (strtolower($managerDepartment->name) === 'further processing')
                                <th>Line</th>
                            @endif
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
                                @if (strtolower($managerDepartment->name) === 'further processing')
                                    <td>{{ $attendance?->line?->name ?? '-' }}</td>
                                @endif
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
                                <td colspan="{{ strtolower($managerDepartment->name) === 'slaughter house' ? 9 : 8 }}"
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
        const managerDepartmentId = "{{ $managerDepartment->id }}";

        $(function() {

            loadCostCenters();

            $('#cost_center_id').change(function() {

                loadPsGroups();

            });

        });

        function loadCostCenters() {

            $.get('/attendance/cost-centers/' + managerDepartmentId, function(res) {

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
