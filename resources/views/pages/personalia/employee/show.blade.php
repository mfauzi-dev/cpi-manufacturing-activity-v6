@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>{{ $area->name }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.employee.index') }}">Area</a></div>
            <div class="breadcrumb-item">{{ $area->name }}</div>
        </div>
    </div>

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session('errors') && count(session('errors')) > 0)
        <div class="alert alert-warning">
            <strong>Beberapa baris dilewati saat import:</strong>
            <ul class="mb-0 mt-1">
                @foreach (session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Daftar Karyawan</h4>
            <div class="card-header-action">
                <a href="#" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Import Excel
                </a>
                <a href="#" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Karyawan
                </a>
            </div>
        </div>
        <div class="card-body">

            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.employee.detail', $area->id) }}" class="form-row mb-3">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIK..."
                        class="form-control">
                </div>

                <div class="col-md-3 mb-2">
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

                <div class="col-md-3 mb-2">
                    <select name="shift_name" class="form-control">
                        <option value="">Semua Shift</option>
                        @foreach ($shiftOptions as $shift)
                            <option value="{{ $shift }}" {{ request('shift_name') == $shift ? 'selected' : '' }}>
                                {{ $shift }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>

                @if ($search || request('employment_status') || request('shift_name'))
                    <div class="col-12">
                        <a href="{{ route('admin.employee.detail', $area->id) }}" class="text-muted small">
                            <i class="fas fa-times"></i> Reset filter
                        </a>
                    </div>
                @endif
            </form>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Outsourcing</th>
                            <th>Status</th>
                            <th>Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->nik }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->outsourcing?->name ?? '-' }}</td>
                                <td>
                                    @if ($employee->employment_status)
                                        <span class="badge badge-info">{{ $employee->employment_status }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $employee->shift_name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Tidak ada data karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $employees->links() }}
        </div>
    </div>
@endsection
