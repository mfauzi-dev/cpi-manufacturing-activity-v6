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
                <form method="GET" action="{{ route('admin.attendance.index') }}">
                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="department_id" class="form-control">
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
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="alfa" {{ request('status') == 'alfa' ? 'selected' : '' }}>Alfa</option>
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
                            <select name="group_id" class="form-control">
                                <option value="">Semua Group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}"
                                        {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
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
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">Reset</a>
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
                                        <span class="badge badge-danger">Alfa</span>
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
