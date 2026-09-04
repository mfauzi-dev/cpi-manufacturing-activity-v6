@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Produktivitas Karyawan</h1>
    </div>

    <div class="section-body">

        <div class="row mb-2">
            <div class="col-md-3">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="text-muted">
                            Department
                        </div>
                        <h3 class="mb-0">
                            {{ $managerDepartment->name }}
                        </h3>
                    </div>
                </div>
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
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employees as $employee)
                            <tr>

                                <td>{{ $employee->nik }}</td>

                                <td>{{ $employee->name }}</td>

                                <td class="text-right">
                                    <a href="{{ route('manager.employee-productivity.detail', $employee->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Produktivitas
                                    </a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center">
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
