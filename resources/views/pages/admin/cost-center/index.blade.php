@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Cost Center</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Cost Center</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    <div class="section-body">
        <a href="{{ route('admin.cost-center.create') }}" class="btn btn-primary mb-4">
            <i class="fas fa-plus"></i> Tambah Cost Center
        </a>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('danger'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('danger') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.cost-center.index') }}" method="GET">
                <div class="row">
                    <div class="col-lg col-md-6 mb-3">
                        <select name="department_id" class="form-control">
                            <option value="">-- Semua Department --</option>

                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg col-md-6 mb-3">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari code / nama cost center..." value="{{ $search }}">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('admin.cost-center.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Daftar Cost Center</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="60">No.</th>
                            <th>Department</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th width="180" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($costCenters as $index => $costCenter)
                            <tr>
                                <td>{{ $costCenters->firstItem() + $index }}</td>
                                <td>{{ $costCenter->department->name }}</td>
                                <td>{{ $costCenter->code }}</td>
                                <td>{{ $costCenter->name }}</td>

                                <td class="text-center">
                                    <a href="{{ route('admin.cost-center.edit', $costCenter->id) }}"
                                        class="btn btn-warning btn-sm">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.cost-center.destroy', $costCenter->id) }}" method="POST"
                                        style="display:inline;">
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
                                <td colspan="5" class="text-center">
                                    Data Cost Center belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        <div class="card-footer text-right">
            {{ $costCenters->withQueryString()->links() }}
        </div>
    </div>
@endsection
