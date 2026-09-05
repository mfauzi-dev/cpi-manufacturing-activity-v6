@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Produktivitas Karyawan</h1>
    </div>

    <div class="section-body">

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-primary mb-0">
                    <div class="card-body">
                        <div class="text-muted">
                            Department
                        </div>
                        <h3 class="mb-0">
                            {{ $department->name }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ url()->current() }}">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>Cost Center</label>

                                <select name="cost_center_id" class="form-control">
                                    <option value="">
                                        Semua Cost Center
                                    </option>

                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ request('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->code }} - {{ $costCenter->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>Search Karyawan</label>

                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="Cari nama atau NIK...">

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-0 mt-4">

                                <button type="submit" class="btn btn-primary">
                                    Filter
                                </button>

                                <a href="{{ url()->current() }}" class="btn btn-secondary">
                                    Reset
                                </a>

                            </div>
                        </div>

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
                            <th>Cost Center</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>

                                <td>
                                    {{ $employee->nik }}
                                </td>

                                <td>
                                    {{ $employee->name }}
                                </td>

                                <td>
                                    {{ $employee->costCenter->code ?? '-' }}
                                    -
                                    {{ $employee->costCenter->name ?? '-' }}
                                </td>

                                <td class="text-right">

                                    <a href="{{ route('manager.employee-productivity.detail', $employee->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Produktivitas
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center">
                                    Tidak ada data employee
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
@endsection
