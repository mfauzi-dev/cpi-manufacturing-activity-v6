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
                        <button class="btn btn-primary">
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
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->nik ?? '-' }}</td>
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
