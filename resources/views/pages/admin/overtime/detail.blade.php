@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Overtime</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Informasi Karyawan</h4>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <strong>NIK</strong>
                        <p>{{ $overtime->employee->nik }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Nama</strong>
                        <p>{{ $overtime->employee->name }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Department</strong>
                        <p>{{ $overtime->employee->department->name ?? '-' }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Status Karyawan</strong>
                        <p>{{ $overtime->employee->status }}</p>
                    </div>

                </div>
            </div>
        </div>

        {{-- DETAIL EARNING --}}
        <div class="card">
            <div class="card-header">
                <h4>Detail Overtime</h4>
            </div>

            <div class="card-body">

                <table class="table table-bordered">
                    <tr>
                        <th width="250">Tanggal Kerja</th>
                        <td>{{ $overtime->work_date }}</td>
                    </tr>

                    <tr>
                        <th>Hours</th>
                        <td>{{ $overtime->hours }}</td>
                    </tr>

                    <tr>
                        <th>Rate Per Hour</th>
                        <td>{{ $overtime->rate_per_hour }}</td>
                    </tr>

                    <tr>
                        <th>Amount</th>
                        <td>
                            Rp {{ number_format($overtime->amount, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            @if ($overtime->status == 'APPROVED')
                                <span class="badge badge-success">Approved</span>
                            @elseif($overtime->status == 'REJECTED')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">
                                    Pending
                                </span>
                            @endif
                        </td>
                    </tr>

                </table>

            </div>

            <div class="card-footer">

                <a href="{{ route('admin.overtime.edit', $overtime->id) }}" class="btn btn-warning">
                    Edit
                </a>

                <a href="{{ route('admin.overtime.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </div>
        </div>

    </div>
@endsection
