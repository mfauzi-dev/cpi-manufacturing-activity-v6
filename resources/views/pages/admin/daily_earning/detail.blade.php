@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Daily Earning</h1>
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
                        <p>{{ $dailyEarning->employee->nik }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Nama</strong>
                        <p>{{ $dailyEarning->employee->name }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Department</strong>
                        <p>{{ $dailyEarning->employee->department->name ?? '-' }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Status Karyawan</strong>
                        <p>{{ $dailyEarning->employee->status }}</p>
                    </div>

                </div>
            </div>
        </div>

        {{-- DETAIL EARNING --}}
        <div class="card">
            <div class="card-header">
                <h4>Detail Daily Earning</h4>
            </div>

            <div class="card-body">

                <table class="table table-bordered">
                    <tr>
                        <th width="250">Tanggal Kerja</th>
                        <td>{{ $dailyEarning->work_date }}</td>
                    </tr>

                    <tr>
                        <th>Kg</th>
                        <td>{{ $dailyEarning->kg }}</td>
                    </tr>

                    <tr>
                        <th>Amount</th>
                        <td>
                            Rp {{ number_format($dailyEarning->amount, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Source</th>
                        <td>{{ $dailyEarning->source }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            @if ($dailyEarning->status == 'APPROVED')
                                <span class="badge badge-success">Approved</span>
                            @elseif($dailyEarning->status == 'REJECTED')
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

                <a href="{{ route('admin.daily-earning.edit', $dailyEarning->id) }}" class="btn btn-warning">
                    Edit
                </a>

                <a href="{{ route('admin.daily-earning.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </div>
        </div>

    </div>
@endsection
