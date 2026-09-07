@extends('layouts.master')

@section('content')

    <div class="section-header">
        <h1>Detail Overtime</h1>
    </div>

    <div class="section-body">

        @php
            $departmentName = strtolower(auth()->user()->department?->name ?? '');
            $isGeneralAffair = $departmentName === 'general affair';
        @endphp

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="row">

            <div class="col-md-8">

                <div class="card">

                    <div class="card-header">

                        <h4>
                            <i class="fas fa-user-clock mr-2"></i>
                            Informasi Overtime
                        </h4>

                        <div class="card-header-action">

                            @if ($overtime->status === 'PENDING')
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock mr-1"></i>
                                    PENDING
                                </span>
                            @elseif ($overtime->status === 'APPROVED')
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    APPROVED
                                </span>
                            @elseif ($overtime->status === 'REJECTED')
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    REJECTED
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    {{ ucfirst($overtime->status ?? '-') }}
                                </span>
                            @endif

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="text-muted">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Tanggal
                                    </label>

                                    <div class="font-weight-bold">
                                        {{ \Carbon\Carbon::parse($overtime->date)->translatedFormat('d F Y') }}
                                    </div>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="text-muted">
                                        <i class="fas fa-calendar-day mr-1"></i>
                                        Hari
                                    </label>

                                    <div class="font-weight-bold">
                                        {{ \Carbon\Carbon::parse($overtime->date)->translatedFormat('l') }}
                                    </div>

                                </div>

                            </div>

                        </div>

                        <hr>

                        <div class="row">

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="text-muted">
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        Jam Mulai
                                    </label>

                                    <div class="h5 mb-0">
                                        {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}
                                    </div>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="text-muted">
                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                        Jam Selesai
                                    </label>

                                    <div class="h5 mb-0">
                                        {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="row mt-2">

                            <div class="col-md-6">

                                <div class="card bg-light mb-0">

                                    <div class="card-body text-center">

                                        <div class="text-muted small">
                                            TOTAL JAM ACTUAL
                                        </div>

                                        <div class="h3 mb-0">
                                            {{ number_format($overtime->total_hours_actual, 2, ',', '.') }}
                                            <small class="text-muted">Jam</small>
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="card bg-light mb-0">

                                    <div class="card-body text-center">

                                        <div class="text-muted small">
                                            TOTAL JAM KONVERSI
                                        </div>

                                        <div class="h3 mb-0">
                                            {{ number_format($overtime->total_hours_konversi, 2, ',', '.') }}
                                            <small class="text-muted">Jam</small>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card">

                    <div class="card-header">

                        <h4>
                            <i class="fas fa-calculator mr-2"></i>
                            Perhitungan Overtime
                        </h4>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="text-muted">
                                        Rate Overtime
                                    </label>

                                    <div class="h4 mb-0">
                                        Rp {{ number_format($overtime->hourly_rate, 0, ',', '.') }}
                                        <small class="text-muted">/ jam</small>
                                    </div>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label class="text-muted">
                                        Total Overtime
                                    </label>

                                    <div class="h4 text-success mb-0">
                                        Rp {{ number_format($overtime->overtime_amount, 0, ',', '.') }}
                                    </div>

                                </div>

                            </div>

                        </div>

                        <hr>

                        <div class="text-center">

                            <div class="text-muted mb-2">
                                Rumus Perhitungan
                            </div>

                            <div class="h5">

                                {{ number_format($overtime->total_hours_actual, 2, ',', '.') }}
                                Jam

                                <span class="mx-2">×</span>

                                Rp {{ number_format($overtime->hourly_rate, 0, ',', '.') }}

                                <span class="mx-2">=</span>

                                <strong class="text-success">
                                    Rp {{ number_format($overtime->overtime_amount, 0, ',', '.') }}
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

                @if ($overtime->description)
                    <div class="card">

                        <div class="card-header">

                            <h4>
                                <i class="fas fa-align-left mr-2"></i>
                                Keterangan
                            </h4>

                        </div>

                        <div class="card-body">

                            <div class="alert alert-light border mb-0">
                                {{ $overtime->description }}
                            </div>

                        </div>

                    </div>
                @endif

            </div>

            <div class="col-md-4">

                <div class="card">

                    <div class="card-header">

                        <h4>
                            <i class="fas fa-user mr-2"></i>
                            Data Karyawan
                        </h4>

                    </div>

                    <div class="card-body">

                        <div class="text-center mb-4">

                            <div class="avatar avatar-xl mb-3">

                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 80px; height: 80px; font-size: 30px;">

                                    <i class="fas fa-user"></i>

                                </div>

                            </div>

                            <h5 class="mb-1">
                                {{ $overtime->employee->name ?? '-' }}
                            </h5>

                            <div class="text-muted">
                                {{ $overtime->employee->nik ?? '-' }}
                            </div>

                        </div>

                        <div class="border-top pt-3">

                            <div class="mb-3">

                                <div class="text-muted small">
                                    Department
                                </div>

                                <div class="font-weight-bold">
                                    {{ $overtime->employee->department->name ?? '-' }}
                                </div>

                            </div>

                            <div class="mb-3">

                                <div class="text-muted small">
                                    Cost Center
                                </div>

                                <div class="font-weight-bold">

                                    @if ($overtime->employee->costCenter)
                                        {{ $overtime->employee->costCenter->code }}
                                        -
                                        {{ $overtime->employee->costCenter->name }}
                                    @else
                                        -
                                    @endif

                                </div>

                            </div>

                            <div class="mb-3">

                                <div class="text-muted small">
                                    PS Group
                                </div>

                                <div class="font-weight-bold">
                                    {{ $overtime->employee->psGroup->name ?? '-' }}
                                </div>

                            </div>

                            <div class="mb-3">

                                <div class="text-muted small">
                                    Position
                                </div>

                                <div class="font-weight-bold">
                                    {{ $overtime->employee->position->name ?? '-' }}
                                </div>

                            </div>

                            <div class="mb-3">

                                <div class="text-muted small">
                                    Level
                                </div>

                                <div class="font-weight-bold">
                                    {{ $overtime->employee->level->name ?? '-' }}
                                </div>

                            </div>

                            <div>

                                <div class="text-muted small">
                                    Status Karyawan
                                </div>

                                @if ($overtime->employee->employee_status === 'cpi')
                                    <span class="badge badge-primary">
                                        CPI
                                    </span>
                                @elseif ($overtime->employee->employee_status === 'harian')
                                    <span class="badge badge-info">
                                        Harian
                                    </span>
                                @elseif ($overtime->employee->employee_status === 'borongan')
                                    <span class="badge badge-success">
                                        Borongan
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        {{ $overtime->employee->employee_status ?? '-' }}
                                    </span>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card">

                    <div class="card-header">

                        <h4>
                            <i class="fas fa-user-check mr-2"></i>
                            Approval
                        </h4>

                    </div>

                    <div class="card-body">

                        @if ($overtime->status === 'PENDING' && $isGeneralAffair)
                            <div class="text-center">

                                <div class="mb-3">

                                    <i class="fas fa-hourglass-half text-warning" style="font-size: 45px;"></i>

                                </div>

                                <h6>
                                    Menunggu Approval
                                </h6>

                                <p class="text-muted">
                                    Overtime ini menunggu persetujuan General Affair.
                                </p>

                                <div class="mt-4">

                                    <button type="button" class="btn btn-success btn-block" onclick="confirmApprove()">

                                        <i class="fas fa-check mr-1"></i>
                                        Approve Overtime

                                    </button>

                                    <button type="button" class="btn btn-danger btn-block" onclick="confirmReject()">

                                        <i class="fas fa-times mr-1"></i>
                                        Reject Overtime

                                    </button>

                                </div>

                            </div>
                        @elseif ($overtime->status === 'PENDING')
                            <div class="text-center">

                                <div class="mb-3">

                                    <i class="fas fa-hourglass-half text-warning" style="font-size: 45px;"></i>

                                </div>

                                <h6>
                                    Menunggu Approval
                                </h6>

                                <p class="text-muted mb-0">
                                    Overtime ini masih menunggu persetujuan.
                                </p>

                            </div>
                        @elseif ($overtime->status === 'APPROVED')
                            <div class="text-center">

                                <div class="mb-3">

                                    <i class="fas fa-check-circle text-success" style="font-size: 45px;"></i>

                                </div>

                                <h6>
                                    Overtime Disetujui
                                </h6>

                                @if ($overtime->approver)
                                    <p class="text-muted mb-1">
                                        Disetujui oleh
                                    </p>

                                    <strong>
                                        {{ $overtime->approver->name }}
                                    </strong>
                                @endif

                                @if ($overtime->APPROVED_at)
                                    <div class="text-muted small mt-2">
                                        {{ \Carbon\Carbon::parse($overtime->APPROVED_at)->translatedFormat('d F Y H:i') }}
                                    </div>
                                @endif

                            </div>
                        @elseif ($overtime->status === 'REJECTED')
                            <div class="text-center">

                                <div class="mb-3">

                                    <i class="fas fa-times-circle text-danger" style="font-size: 45px;"></i>

                                </div>

                                <h6>
                                    Overtime Ditolak
                                </h6>

                                @if ($overtime->approver)
                                    <p class="text-muted mb-1">
                                        Diproses oleh
                                    </p>

                                    <strong>
                                        {{ $overtime->approver->name }}
                                    </strong>
                                @endif

                                @if ($overtime->APPROVED_at)
                                    <div class="text-muted small mt-2">
                                        {{ \Carbon\Carbon::parse($overtime->APPROVED_at)->translatedFormat('d F Y H:i') }}
                                    </div>
                                @endif

                            </div>
                        @elseif ($overtime->status === 'paid')
                            <div class="text-center">

                                <div class="mb-3">

                                    <i class="fas fa-money-bill-wave text-primary" style="font-size: 45px;"></i>

                                </div>

                                <h6>
                                    Overtime Sudah Dibayar
                                </h6>

                                @if ($overtime->approver)
                                    <p class="text-muted mb-1">
                                        Disetujui oleh
                                    </p>

                                    <strong>
                                        {{ $overtime->approver->name }}
                                    </strong>
                                @endif

                                @if ($overtime->APPROVED_at)
                                    <div class="text-muted small mt-2">
                                        {{ \Carbon\Carbon::parse($overtime->APPROVED_at)->translatedFormat('d F Y H:i') }}
                                    </div>
                                @endif

                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>

        <div class="card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <a href="{{ route('general-manager.overtime.index') }}" class="btn btn-secondary">

                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali

                    </a>

                    @if ($overtime->status === 'PENDING' && $isGeneralAffair)
                        <div>

                            <button type="button" class="btn btn-success mr-2" onclick="confirmApprove()">

                                <i class="fas fa-check mr-1"></i>
                                Approve

                            </button>

                            <button type="button" class="btn btn-danger" onclick="confirmReject()">

                                <i class="fas fa-times mr-1"></i>
                                Reject

                            </button>

                        </div>
                    @endif

                </div>

            </div>

        </div>

        @if ($overtime->status === 'PENDING' && $isGeneralAffair)
            <form id="approve-form" action="{{ route('general-manager.overtime.approve', $overtime->id) }}"
                method="POST" class="d-none">

                @csrf
                @method('PUT')

            </form>

            <form id="reject-form" action="{{ route('general-manager.overtime.reject', $overtime->id) }}" method="POST"
                class="d-none">

                @csrf
                @method('PUT')

            </form>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        function confirmApprove() {

            if (confirm('Yakin ingin menyetujui overtime ini?')) {

                document.getElementById('approve-form').submit();

            }

        }

        function confirmReject() {

            if (confirm('Yakin ingin menolak overtime ini?')) {

                document.getElementById('reject-form').submit();

            }

        }
    </script>
@endpush
