@extends('layouts.master')

@section('content')
    <div class="section-header">

        <h1>Overtime Rate</h1>

        <div class="section-header-breadcrumb">

            <div class="breadcrumb-item active">
                Overtime Rate
            </div>

        </div>

    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Filter Overtime Rate</h4>
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('manager.overtime-rate.index') }}">

                    <div class="row">

                        <div class="col-md-3">

                            <div class="form-group">
                                <label>Tahun</label>

                                <select name="tahun" class="form-control">

                                    <option value="">
                                        Semua Tahun
                                    </option>

                                    @for ($year = date('Y') + 1; $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}"
                                            {{ (string) $tahun === (string) $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor

                                </select>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="form-group">
                                <label>Employee Status</label>

                                <select name="employee_status" class="form-control">

                                    <option value="">
                                        Semua Status
                                    </option>

                                    <option value="cpi" {{ $employeeStatus === 'cpi' ? 'selected' : '' }}>
                                        CPI
                                    </option>

                                    <option value="borongan" {{ $employeeStatus === 'borongan' ? 'selected' : '' }}>
                                        Borongan
                                    </option>

                                    <option value="harian" {{ $employeeStatus === 'harian' ? 'selected' : '' }}>
                                        Harian
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="form-group">
                                <label>Level</label>

                                <select name="level_id" class="form-control">

                                    <option value="">
                                        Semua Level
                                    </option>

                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}"
                                            {{ (string) $levelId === (string) $level->id ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="form-group">
                                <label>Position</label>

                                <select name="position_id" class="form-control">

                                    <option value="">
                                        Semua Position
                                    </option>

                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}"
                                            {{ (string) $positionId === (string) $position->id ? 'selected' : '' }}>
                                            {{ $position->name }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="form-group">
                                <label>Search</label>

                                <input type="text" name="search" class="form-control" value="{{ $search }}"
                                    placeholder="Cari employee status, level, atau position...">


                            </div>

                        </div>



                    </div>

                    <div class="text-left">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filter
                        </button>

                        <a href="{{ route('manager.overtime-rate.index') }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>

        </div>

        <div class="card">

            <div class="card-header">

                <h4>Data Overtime Rate</h4>

                <div class="card-header-action">

                    <a href="{{ route('manager.overtime-rate.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Overtime Rate
                    </a>

                </div>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th width="60">No</th>

                                <th>Tahun</th>

                                <th>Employee Status</th>

                                <th>Level</th>

                                <th>Position</th>

                                <th>Rate</th>

                                <th width="150">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($overtimeRates as $overtimeRate)
                                <tr>

                                    <td>
                                        {{ $overtimeRates->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        {{ $overtimeRate->tahun }}
                                    </td>

                                    <td>
                                        @if ($overtimeRate->employee_status === 'cpi')
                                            <span class="badge badge-primary">
                                                CPI
                                            </span>
                                        @elseif ($overtimeRate->employee_status === 'borongan')
                                            <span class="badge badge-warning">
                                                Borongan
                                            </span>
                                        @elseif ($overtimeRate->employee_status === 'harian')
                                            <span class="badge badge-info">
                                                Harian
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                {{ $overtimeRate->employee_status }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $overtimeRate->level->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $overtimeRate->position->name ?? '-' }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($overtimeRate->rate, 0, ',', '.') }}
                                    </td>

                                    <td>

                                        <a href="{{ route('manager.overtime-rate.edit', $overtimeRate->id) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('manager.overtime-rate.destroy', $overtimeRate->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus Overtime Rate ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="text-center">

                                        <div class="py-4">

                                            <i class="fas fa-clock fa-2x text-muted mb-2"></i>

                                            <p class="mb-0">
                                                Belum ada data Overtime Rate.
                                            </p>

                                        </div>

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                    <div class="card-footer text-right">
                        {{ $overtimeRates->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
