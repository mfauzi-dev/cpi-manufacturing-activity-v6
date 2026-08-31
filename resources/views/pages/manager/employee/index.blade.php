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

                <form action="{{ route('manager.employee.index') }}" method="GET">

                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <select name="employment_status" class="form-control">
                                <option value="">Jenis Karyawan</option>

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

                        <div class="col-md-3 mb-2">
                            <select name="employee_status" class="form-control">
                                <option value="">Status</option>

                                <option value="cpi" {{ request('employee_status') == 'cpi' ? 'selected' : '' }}>
                                    CPI
                                </option>

                                <option value="borongan" {{ request('employee_status') == 'borongan' ? 'selected' : '' }}>
                                    Borongan
                                </option>

                                <option value="harian" {{ request('employee_status') == 'harian' ? 'selected' : '' }}>
                                    Harian
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="is_active" class="form-control">
                                <option value="">Status Aktif</option>

                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>
                                    Aktif
                                </option>

                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="cost_center_id" class="form-control">
                                <option value="">Semua Cost Center</option>

                                @foreach ($costCenterList as $costCenter)
                                    <option value="{{ $costCenter->id }}"
                                        {{ request('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                        {{ $costCenter->code }} - {{ $costCenter->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="position_id" class="form-control">
                                <option value="">Semua Position</option>

                                @foreach ($positionList as $position)
                                    <option value="{{ $position->id }}"
                                        {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <input type="text" name="search" class="form-control" placeholder="NIK / Nama"
                                value="{{ request('search') }}">
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Terapkan Filter
                        </button>

                        <a href="{{ route('manager.employee.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>

            </div>
        </div>

        {{-- TABLE --}}
        <div class="card">

            <div class="card-header">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Data Karyawan</h4>

                    <a href="{{ route('manager.employee.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Karyawan
                    </a>
                </div>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Status Aktif</th>
                            <th>Cost Center</th>
                            <th>PS Group</th>
                            <th>Position</th>
                            <th>Gender</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>

                                <td>
                                    {{ $employee->nik ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->name }}
                                </td>

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

                                <td>
                                    @if ($employee->is_active)
                                        <span class="badge badge-success">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $employee->costCenter->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->psGroup->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->position->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employee->gender ?? '-' }}
                                </td>

                                <td>

                                    <a href="{{ route('manager.employee.detail', $employee->id) }}"
                                        class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('manager.employee.edit', $employee->id) }}"
                                        class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('manager.employee.destroy', $employee->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus data karyawan ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="9" class="text-center text-muted">
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
