@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Attendance</h1>
    </div>

    <div class="section-body">

        <div class="mb-3">
            <a href="{{ route('admin.attendance.summary-all-department', $filters) }}">
                &larr; Kembali ke Summary
            </a>
        </div>

        {{-- INFO KARYAWAN --}}
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

                <div>
                    <h5 class="mb-1">{{ $employee->name }}</h5>
                    <span class="text-muted">
                        NIK {{ $employee->nik }} &middot;
                        {{ $employee->department->name ?? '-' }} &middot;
                        {{ \Carbon\Carbon::createFromDate($year, $monthNum, 1)->translatedFormat('F Y') }}
                    </span>
                </div>

                <div class="d-flex" style="gap: 8px;">

                    @foreach (['hadir' => 'success', 'izin' => 'warning', 'sakit' => 'info', 'cuti' => 'primary', 'alfa' => 'danger'] as $status => $color)
                        <div class="text-center px-3">
                            <div class="text-muted" style="font-size: 12px; text-transform: capitalize;">
                                {{ $status }}
                            </div>
                            <span class="badge badge-{{ $color }}">
                                {{ $attendances->where('status', $status)->count() }}
                            </span>
                        </div>
                    @endforeach

                </div>

            </div>
        </div>

        {{-- TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($attendances as $attendance)
                            <tr>

                                <td>{{ $attendance->date->translatedFormat('d M Y') }}</td>

                                <td>{{ $attendance->date->translatedFormat('l') }}</td>

                                <td>

                                    @if ($attendance->status == 'hadir')
                                        <span class="badge badge-success">Hadir</span>
                                    @elseif ($attendance->status == 'izin')
                                        <span class="badge badge-warning">Izin</span>
                                    @elseif ($attendance->status == 'sakit')
                                        <span class="badge badge-info">Sakit</span>
                                    @elseif ($attendance->status == 'cuti')
                                        <span class="badge badge-primary">Cuti</span>
                                    @elseif ($attendance->status == 'alfa')
                                        <span class="badge badge-danger">Alfa</span>
                                    @endif

                                </td>

                                <td>{{ $attendance->keterangan_izin ?? '-' }}</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center">
                                    Tidak ada data attendance untuk bulan ini
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>

    </div>
@endsection
