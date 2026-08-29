@extends('layouts.master')

@section('title', 'Production Dashboard')

@section('content')

    <div class="section-header">
        <h1>Production Dashboard</h1>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-industry"></i>
        Selamat datang <strong>{{ auth()->user()->name }}</strong>
        <br>
        <small>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</small>
    </div>

    <div class="alert" style="background-color:#36a9e1; color:#fff;">
        Department: <strong>{{ auth()->user()->department->name ?? '-' }}</strong>
    </div>

    {{-- ===== Cards ringkasan ===== --}}
    <div class="row">
        <div class="col-md-2-4 col-sm-6 col-12 mb-4" style="flex:0 0 20%; max-width:20%;">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Total karyawan</small>
                    <h3 class="mb-0">{{ $totalKaryawan }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2-4 col-sm-6 col-12 mb-4" style="flex:0 0 20%; max-width:20%;">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Hadir hari ini</small>
                    <h3 class="mb-0 text-success">{{ $attendanceToday->hadir ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2-4 col-sm-6 col-12 mb-4" style="flex:0 0 20%; max-width:20%;">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Izin / sakit / cuti</small>
                    <h3 class="mb-0 text-warning">{{ $attendanceToday->izin_sakit_cuti ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2-4 col-sm-6 col-12 mb-4" style="flex:0 0 20%; max-width:20%;">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Alfa hari ini</small>
                    <h3 class="mb-0 text-danger">{{ $attendanceToday->alfa ?? 0 }}</h3>
                </div>
            </div>
        </div>

        @if (strtolower(auth()->user()->department->name) === 'sausage')
            <div class="col-md-2-4 col-sm-6 col-12 mb-4" style="flex:0 0 20%; max-width:20%;">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted">Output hari ini</small>
                        <h3 class="mb-0">{{ number_format($outputHariIniSosis, 0, ',', '.') }} kg</h3>
                    </div>
                </div>
            </div>
        @endif

        @if (strtolower(auth()->user()->department->name) === 'further processing')
            <div class="col-md-2-4 col-sm-6 col-12 mb-4" style="flex:0 0 20%; max-width:20%;">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted">Output hari ini</small>
                        <h3 class="mb-0">{{ number_format($outputHariIniFurther, 0, ',', '.') }} kg</h3>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ===== Chart tren ===== --}}
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card">
                <div class="card-header">
                    Tren kehadiran 7 hari terakhir
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-header">
                    Output produksi (kg)
                </div>
                <div class="card-body">
                    <canvas id="outputChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Perlu perhatian & shortcut ===== --}}
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card">
                <div class="card-header">
                    Perlu perhatian hari ini
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tbody>
                            @forelse ($alfaHariIni as $emp)
                                <tr>
                                    <td>
                                        <i class="fas fa-exclamation-triangle text-danger"></i>
                                        {{ $emp->name }} <span class="text-muted">({{ $emp->nik }})</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-danger">Alfa</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">Tidak ada karyawan alfa hari ini.</td>
                                </tr>
                            @endforelse

                            @if ($belumDiabsen > 0)
                                <tr>
                                    <td>
                                        <i class="fas fa-clock text-warning"></i>
                                        {{ $belumDiabsen }} karyawan belum diabsen hari ini
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-warning">Pending</span>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="d-flex flex-column">
                <a href="{{ route('admin-production.attendance.create') }}" class="btn btn-outline-primary mb-2 text-left">
                    <i class="fas fa-clipboard-check"></i> Tambah absensi
                </a>
                <a href="{{ route('admin-production.daily-activity.create') }}"
                    class="btn btn-outline-primary mb-2 text-left">
                    <i class="fas fa-plus"></i> Tambah daily activity
                </a>
                <a href="{{ route('admin-production.daily-activity.index') }}" class="btn btn-outline-primary text-left">
                    <i class="fas fa-chart-bar"></i> Lihat summary
                </a>
            </div>
        </div>
    </div>

    {{-- ===== Ringkasan cost center ===== --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    Ringkasan cost center hari ini
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cost center</th>
                                <th class="text-right">Output (kg)</th>
                                <th class="text-right">Harga/kg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (strtolower(auth()->user()->department->name) === 'sausage')

                                @forelse ($costCenterSummary as $cc)
                                    <tr>
                                        <td>{{ $cc->cost_center_name }}</td>
                                        <td class="text-right">{{ number_format($cc->total_kg, 0, ',', '.') }}</td>
                                        <td class="text-right">Rp {{ number_format($cc->harga_per_kg, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">Belum ada data daily activity hari ini.</td>
                                    </tr>
                                @endforelse

                            @endif

                            @if (strtolower(auth()->user()->department->name) === 'further processing')

                                @forelse ($costCenterSummaryFurther as $cc)
                                    <tr>
                                        <td>{{ $cc->cost_center_name }}</td>
                                        <td class="text-right">{{ number_format($cc->total_kg, 0, ',', '.') }}</td>
                                        <td class="text-right">Rp {{ number_format($cc->harga_per_kg, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">Belum ada data daily activity hari ini.</td>
                                    </tr>
                                @endforelse

                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const trendLabels = @json($trendLabels);
        const trendHadir = @json($trendHadir);
        const trendAlfa = @json($trendAlfa);
        @if (strtolower(auth()->user()->department->name) === 'further processing')
            const trendOutputKg = @json($trendOutputKgFurther);
        @endif

        @if (strtolower(auth()->user()->department->name) === 'sausage')
            const trendOutputKg = @json($trendOutputKgSosis);
        @endif

        new Chart(document.getElementById('attendanceChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                        label: 'Hadir',
                        data: trendHadir,
                        borderColor: '#1a7a4c',
                        backgroundColor: 'rgba(26,122,76,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Alfa',
                        data: trendAlfa,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
            },
        });

        new Chart(document.getElementById('outputChart'), {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Output (kg)',
                    data: trendOutputKg,
                    backgroundColor: '#36a9e1',
                }, ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
            },
        });
    </script>
@endpush
