@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Payroll Bulanan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Payroll</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    {{-- FLASH MESSAGE --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payroll.bulanan.index') }}">
                <div class="row">

                    <div class="col-lg mb-2">
                        <select name="month" class="form-control">
                            <option value="">Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-lg mb-2">
                        <select name="year" class="form-control">
                            <option value="">Tahun</option>
                            @for ($i = now()->year - 2; $i <= now()->year; $i++)
                                <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- BUTTON --}}
                    <div class="col-lg-2 mb-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daftar Periode Payroll Bulanan</h4>

            @if (auth()->user()->role->name === 'SUPER_ADMIN' || auth()->user()->role->name === 'FINANCE')
                <a href="{{ route('admin.payroll.bulanan.generate.form') }}" class="btn btn-primary">
                    <i class="fas fa-cogs"></i> Generate Payroll
                </a>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="60">No.</th>
                            <th>Periode</th>
                            <th class="text-center">Total Karyawan</th>
                            <th>Total Gross</th>
                            <th>Total Net</th>
                            <th class="text-center">Status</th>
                            <th>Generated At</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $index => $period)
                            @php
                                $periodLabel = \Carbon\Carbon::create(
                                    $period->period_year,
                                    $period->period_month,
                                )->translatedFormat('F Y');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-weight-bold">{{ $periodLabel }}</td>
                                <td class="text-center">{{ $period->total_karyawan }} orang</td>
                                <td>Rp {{ number_format($period->total_gross) }}</td>
                                <td>Rp {{ number_format($period->total_net) }}</td>
                                <td class="text-center">
                                    @if ($period->has_draft)
                                        <span class="badge badge-warning">DRAFT</span>
                                    @else
                                        <span class="badge badge-success">FINAL</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $period->generated_at ? \Carbon\Carbon::parse($period->generated_at)->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.payroll.bulanan.detail', [$period->period_month, $period->period_year]) }}"
                                        class="btn btn-primary btn-sm mb-2">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>

                                    @if (auth()->user()->role->name === 'SUPER_ADMIN')
                                        @if ($period->has_draft)
                                            <form
                                                action="{{ route('admin.payroll.bulanan.finalize.period', [$period->period_month, $period->period_year]) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-success btn-sm mb-2"
                                                    onclick="return confirm('Finalisasi semua payroll {{ $periodLabel }}? Status tidak bisa dikembalikan ke DRAFT.')">
                                                    <i class="fas fa-check-circle"></i> Finalisasi
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route('admin.payroll.bulanan.destroy.period', [$period->period_month, $period->period_year]) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm mb-2"
                                                    onclick="return confirm('Hapus semua payroll {{ $periodLabel }}?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    Belum ada data payroll bulanan. Silakan generate terlebih dahulu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
