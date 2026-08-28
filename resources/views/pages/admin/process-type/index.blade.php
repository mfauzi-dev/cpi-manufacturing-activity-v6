@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Process Type</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Process Type</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    <div class="section-body">
        <a href="{{ route('admin.process-type.create') }}" class="btn btn-primary mb-4">
            <i class="fas fa-plus"></i> Tambah Process Type
        </a>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.process-type.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-lg mb-3">
                        <select name="department_id" class="form-control">
                            <option value="">Semua Department</option>
                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama Process Type."
                            value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="mt-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.process-type.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Daftar Process Type</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="60">No.</th>
                            <th>Department</th>
                            <th>Nama Process Type</th>
                            <th width="180" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($processTypes as $index => $processType)
                            <tr>
                                <td>{{ $processTypes->firstItem() + $index }}</td>
                                <td>{{ $processType->department->name ?? '-' }}</td>
                                <td>{{ $processType->name }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.process-type.edit', $processType->id) }}"
                                        class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.process-type.destroy', $processType->id) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    Daftar process type belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-right">
            {{ $processTypes->withQueryString()->links() }}
        </div>
    </div>
@endsection
