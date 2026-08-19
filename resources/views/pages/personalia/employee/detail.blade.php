@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Area - {{ $area->name }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.employee.index') }}">Area</a></div>
            <div class="breadcrumb-item">Detail</div>
        </div>
    </div>

    <div class="section-body">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- ACTION BUTTONS --}}
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <a href="{{ route('admin.employee.create', $area->id) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Karyawan
                    </a>

                    <a href="{{ route('admin.employee.import', $area->id) }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                </div>

            </div>
        </div>

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.employee.detail', $area->id) }}" method="GET">

                    <div class="row">

                        {{-- STATUS --}}
                        <div class="col-md-4 mb-2">
                            <select name="employment_status" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}"
                                        {{ request('employment_status') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SHIFT --}}
                        <div class="col-md-4 mb-2">
                            <select name="shift_name" class="form-control">
                                <option value="">Semua Shift</option>
                                @foreach ($shiftOptions as $shift)
                                    <option value="{{ $shift }}"
                                        {{ request('shift_name') == $shift ? 'selected' : '' }}>
                                        {{ $shift }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SEARCH --}}
                        <div class="col-md-4 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Terapkan Filter
                        </button>

                        <a href="{{ route('admin.employee.detail', $area->id) }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Outsourcing</th>
                            <th>Status</th>
                            <th>Shift</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->nik }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->outsourcing->name ?? '-' }}</td>
                                <td>{{ $employee->employment_status ?? '-' }}</td>
                                <td>{{ $employee->shift_name ?? '-' }}</td>

                                <td>
                                    <a href="#" class="btn btn-sm btn-info">
                                        Edit
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

                <div class="card-footer text-right">
                    {{ $employees->withQueryString()->links() }}
                </div>

            </div>
        </div>

    </div>
@endsection
