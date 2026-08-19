@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Overtime</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Overtime</div>
            <div class="breadcrumb-item">Table</div>
        </div>
    </div>

    <a href="{{ route('admin-production.overtime.create') }}" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Overtime
    </a>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('errors'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('errors') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif


    <div class="section-body">
        <div class="card">
            <div class="card-body">

                <form method="GET" action="{{ route('admin-production.overtime.index') }}">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label>Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">Semua Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $departmentId == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>

                                <option value="PENDING" {{ $status == 'PENDING' ? 'selected' : '' }}>
                                    Pending
                                </option>

                                <option value="APPROVED" {{ $status == 'APPROVED' ? 'selected' : '' }}>
                                    Approved
                                </option>

                                <option value="REJECTED" {{ $status == 'REJECTED' ? 'selected' : '' }}>
                                    Rejected
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Nama Karyawan</label>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIK">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="work_date" class="form-control" value="{{ $workDate }}">
                        </div>

                    </div>

                    {{-- BUTTON DI BAWAH KIRI --}}
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Terapkan Filter
                        </button>

                        <a href="{{ route('admin-production.overtime.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>

            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th>Tanggal</th>
                            <th>Hours</th>
                            <th>Rate per Hours</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($overtimes as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->employee->nik ?? '-' }}</td>
                                <td>{{ $item->employee->name ?? '-' }}</td>
                                <td>{{ $item->employee->department->name ?? '-' }}</td>
                                <td>{{ $item->work_date->format('d F Y') }}</td>
                                <td>{{ $item->hours }}</td>
                                <td>Rp {{ number_format($item->rate_per_hour, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>

                                <td>
                                    @if ($item->status == 'APPROVED')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($item->status == 'REJECTED')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">

                                        <a href="{{ route('admin-production.overtime.show', $item->id) }}"
                                            class="btn btn-sm btn-primary mr-2">
                                            Lihat
                                        </a>

                                        <a href="{{ route('admin-production.overtime.edit', $item->id) }}"
                                            class="btn btn-sm btn-info">
                                            Edit
                                        </a>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>
    </div>
@endsection
