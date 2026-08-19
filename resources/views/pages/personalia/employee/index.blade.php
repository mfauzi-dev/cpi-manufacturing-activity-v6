@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Data Karyawan</h1>
    </div>

    <div class="section-body">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form action="{{ route('personalia.employee.index') }}" method="GET">

                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <select name="employment_status" class="form-control">
                                <option value="">Semua Status</option>

                                <option value="permanent"
                                    {{ request('employment_status') == 'permanent' ? 'selected' : '' }}>
                                    Permanent
                                </option>

                                <option value="outsourcing"
                                    {{ request('employment_status') == 'outsourcing' ? 'selected' : '' }}>
                                    Outsourcing
                                </option>
                            </select>
                        </div>

                        <div class="col-md-9 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                value="{{ request('search') }}">
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Terapkan Filter
                        </button>

                        <a href="{{ route('personalia.employee.index') }}" class="btn btn-secondary">
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
                            <th>Status</th>
                            <th>Cost Center</th>
                            <th>PS Group</th>
                            <th>Position</th>
                            <th>Gender</th>
                            <th width="240" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->nik }}</td>
                                <td>{{ $employee->name }}</td>

                                <td>
                                    @if ($employee->employment_status == 'permanent')
                                        <span class="badge badge-primary">
                                            Permanent
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            Outsourcing
                                        </span>
                                    @endif
                                </td>


                                <td>{{ $employee->costCenter->name ?? '-' }}</td>

                                <td>{{ $employee->psGroup->name ?? '-' }}</td>

                                <td>{{ $employee->position->name ?? '-' }}</td>

                                <td>{{ $employee->gender ?? '-' }}</td>

                                <td class="text-center">
                                    <a href="#" class="btn btn-success btn-sm">
                                        Detail
                                    </a>
                                    <a href="#" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <form action="#" method="POST" style="display:inline;">
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
                                <td colspan="12" class="text-center text-muted">
                                    Tidak ada data
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
