@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Summary Attendance - All Department</h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('admin.attendance.summary-all-department') }}">

                    <div class="row">

                        {{-- BULAN --}}
                        <div class="col-md-3 mb-2">
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

                        {{-- TAHUN --}}
                        <div class="col-md-3 mb-2">
                            <select name="year" class="form-control">

                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor

                            </select>
                        </div>

                        {{-- DEPARTMENT --}}
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

                        {{-- OUTSOURCING --}}
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

                        {{-- GROUP --}}
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

                        {{-- SEARCH --}}
                        <div class="col-md-3 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                value="{{ request('search') }}">
                        </div>

                    </div>

                    <div class="mt-2">

                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('admin.attendance.summary-all-department') }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

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
                            <th>Department</th>
                            <th>OS</th>
                            <th>Group</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Cuti</th>
                            <th class="text-center">Alfa</th>
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
                                    <a href="{{ route('admin.attendance.summary-all-department.detail', array_merge(request()->query(), ['employee' => $employee->id])) }}"
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
                {{ $employees->links() }}
            </div>

        </div>

    </div>
@endsection
