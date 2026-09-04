@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Produktivitas Karyawan</h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('general-manager.employee-productivity.list') }}">

                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" class="form-control">

                                    <option value="">Semua Department</option>

                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                    </div>

                    <div>

                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('general-manager.employee-productivity.list') }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        {{-- TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>

                                <td>{{ $employee->nik }}</td>

                                <td>{{ $employee->name }}</td>

                                <td>{{ $employee->department->name ?? '-' }}</td>

                                <td class="text-right">
                                    <a href="{{ route('general-manager.employee-productivity.detail', $employee->id) }}"
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
