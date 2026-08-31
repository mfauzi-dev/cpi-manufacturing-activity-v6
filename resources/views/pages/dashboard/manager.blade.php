@extends('layouts.master')

@section('content')


    <div class="section-header">
        <h1>Dashboard</h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card">
            <div class="card-body">
                <form method="GET">
                    <div class="row">

                        <div class="col-md-4 mb-2">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date"
                                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date"
                                    value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="form-group">
                                <label>Cost Center</label>
                                <select name="cost_center_id" id="cost_center_id" class="form-control">

                                    <option value="">Semua Cost Center</option>

                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ request('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>

                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>


        {{-- EMPLOYEE --}}
        <div class="row">

            <div class="col-lg-6 col-md-6">
                <div class="card card-statistic-1">

                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Employee</h4>
                        </div>

                        <div class="card-body">
                            {{ number_format($totalEmployee) }}
                        </div>
                    </div>

                </div>
            </div>


            <div class="col-lg-6 col-md-6">
                <div class="card card-statistic-1">

                    <div class="card-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Hadir</h4>
                        </div>

                        <div class="card-body">
                            {{ number_format($hadir) }}
                        </div>
                    </div>

                </div>
            </div>

        </div>


        {{-- OUTPUT CARDS --}}
        <div class="row">

            @if (strtolower(auth()->user()->department->name) === 'further processing')
                {{-- FURTHER --}}
                <div class="col-lg-12 col-md-12">
                    <div class="card card-statistic-2">

                        <div class="card-icon shadow-success bg-success">
                            <i class="fas fa-weight"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total KG</h4>
                            </div>

                            <div class="card-body">
                                {{ number_format($totalKgFurther, 2, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>
            @elseif (strtolower(auth()->user()->department->name) === 'slaughter house')
                {{-- SLAUGHTER HOUSE --}}

                {{-- AVERAGE RP/KG --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card card-statistic-2">

                        <div class="card-icon shadow-warning bg-warning">
                            <i class="fas fa-tags"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Average Rp/KG</h4>
                            </div>

                            <div class="card-body">
                                Rp {{ number_format($averageHargaKgSlaughterHouse, 2, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>


                {{-- TOTAL RUPIAH --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card card-statistic-1">

                        <div class="card-icon bg-danger">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Rupiah</h4>
                            </div>

                            <div class="card-body">
                                Rp {{ number_format($totalRupiahSlaughterHouse, 0, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>


                {{-- TOTAL KG --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card card-statistic-2">

                        <div class="card-icon shadow-success bg-success">
                            <i class="fas fa-weight"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total KG</h4>
                            </div>

                            <div class="card-body">
                                {{ number_format($totalKgSlaughterHouse, 2, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>
            @else
                {{-- SOSIS --}}

                {{-- AVERAGE RP/KG --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card card-statistic-2">

                        <div class="card-icon shadow-warning bg-warning">
                            <i class="fas fa-tags"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Average Rp/KG</h4>
                            </div>

                            <div class="card-body">
                                Rp {{ number_format($averageHargaKgSosis, 2, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>


                {{-- TOTAL RUPIAH --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card card-statistic-1">

                        <div class="card-icon bg-danger">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Rupiah</h4>
                            </div>

                            <div class="card-body">
                                Rp {{ number_format($totalRupiahSosis, 0, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>


                {{-- TOTAL KG --}}
                <div class="col-lg-4 col-md-6">
                    <div class="card card-statistic-2">

                        <div class="card-icon shadow-success bg-success">
                            <i class="fas fa-weight"></i>
                        </div>

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total KG</h4>
                            </div>

                            <div class="card-body">
                                {{ number_format($totalKgSosis, 2, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>
            @endif

        </div>


        {{-- CHART --}}
        <div class="row">

            {{-- KG CHART --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-chart-bar mr-2"></i>
                            Output KG per Department
                        </h4>
                    </div>

                    <div class="card-body">
                        <canvas id="kgChart" height="180"></canvas>
                    </div>

                </div>

            </div>


            {{-- RUPIAH CHART --}}
            @if (strtolower(auth()->user()->department->name) !== 'further processing')
                <div class="col-lg-6">

                    <div class="card">

                        <div class="card-header">
                            <h4>
                                <i class="fas fa-chart-line mr-2"></i>
                                Total Rupiah per Department
                            </h4>
                        </div>

                        <div class="card-body">
                            <canvas id="rupiahChart" height="180"></canvas>
                        </div>

                    </div>

                </div>
            @endif


            {{-- TREND --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-chart-area mr-2"></i>
                            Tren Output Harian (kg)
                        </h4>
                    </div>

                    <div class="card-body">
                        <canvas id="outputChart" height="180"></canvas>
                    </div>

                </div>

            </div>

        </div>


        {{-- PROGRESS --}}
        <div class="row">

            {{-- ATTENDANCE --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">
                        <h4>Progress Attendance</h4>
                    </div>

                    <div class="card-body">

                        <div class="mb-2 d-flex justify-content-between">

                            <span>
                                {{ number_format($attendanceProgress, 1) }}%
                            </span>

                            <span>
                                {{ $hadir + $izin + $sakit + $alpha }}
                                /
                                {{ $totalEmployee }}
                            </span>

                        </div>

                        <div class="progress" style="height:20px;">

                            <div class="progress-bar bg-success" role="progressbar"
                                style="width:{{ $attendanceProgress }}%">

                                {{ number_format($attendanceProgress, 1) }}%

                            </div>

                        </div>


                        <div class="mt-4">

                            <table class="table table-sm">

                                <tr>
                                    <td>Hadir</td>
                                    <td class="text-right text-success">
                                        {{ $hadir }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>Izin</td>
                                    <td class="text-right text-warning">
                                        {{ $izin }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>Sakit</td>
                                    <td class="text-right text-info">
                                        {{ $sakit }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>Alpha</td>
                                    <td class="text-right text-danger">
                                        {{ $alpha }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            {{-- DAILY ACTIVITY --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">
                        <h4>Progress Daily Activity</h4>
                    </div>

                    <div class="card-body">

                        @if (strtolower(auth()->user()->department->name) === 'further processing')
                            {{-- FURTHER --}}

                            <div class="mb-2 d-flex justify-content-between">

                                <span>
                                    {{ number_format($dailyActivityProgressFurther, 1) }}%
                                </span>

                            </div>

                            <div class="progress" style="height:20px;">

                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width:{{ $dailyActivityProgressFurther }}%">

                                    {{ number_format($dailyActivityProgressFurther, 1) }}%

                                </div>

                            </div>


                            <div class="mt-4">

                                <table class="table table-sm">

                                    <tr>
                                        <td>Total KG</td>

                                        <td class="text-right">
                                            {{ number_format($totalKgFurther, 2, ',', '.') }}
                                        </td>
                                    </tr>

                                </table>

                            </div>
                        @elseif (strtolower(auth()->user()->department->name) === 'slaughter house')
                            {{-- SLAUGHTER HOUSE --}}

                            <div class="mb-2 d-flex justify-content-between">

                                <span>
                                    {{ number_format($dailyActivityProgressSlaughterHouse, 1) }}%
                                </span>

                            </div>

                            <div class="progress" style="height:20px;">

                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width:{{ $dailyActivityProgressSlaughterHouse }}%">

                                    {{ number_format($dailyActivityProgressSlaughterHouse, 1) }}%

                                </div>

                            </div>


                            <div class="mt-4">

                                <table class="table table-sm">

                                    <tr>
                                        <td>Total KG</td>

                                        <td class="text-right">
                                            {{ number_format($totalKgSlaughterHouse, 2, ',', '.') }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Total Rupiah</td>

                                        <td class="text-right font-weight-bold">
                                            Rp {{ number_format($totalRupiahSlaughterHouse, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                </table>

                            </div>
                        @else
                            {{-- SOSIS --}}

                            <div class="mb-2 d-flex justify-content-between">

                                <span>
                                    {{ number_format($dailyActivityProgressSosis, 1) }}%
                                </span>

                            </div>

                            <div class="progress" style="height:20px;">

                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width:{{ $dailyActivityProgressSosis }}%">

                                    {{ number_format($dailyActivityProgressSosis, 1) }}%

                                </div>

                            </div>


                            <div class="mt-4">

                                <table class="table table-sm">

                                    <tr>
                                        <td>Total KG</td>

                                        <td class="text-right">
                                            {{ number_format($totalKgSosis, 2, ',', '.') }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Total Rupiah</td>

                                        <td class="text-right font-weight-bold">
                                            Rp {{ number_format($totalRupiahSosis, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                </table>

                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- SUMMARY DEPARTMENT --}}
        <div class="card">

            <div class="card-header">
                <h4>Summary Department</h4>
            </div>

            <div class="card-body table-responsive">

                @if (strtolower(auth()->user()->department->name) === 'further processing')
                    {{-- FURTHER --}}

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>Department</th>
                                <th class="text-right">KG</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($departmentSummaryFurther as $department)
                                <tr>
                                    <td>{{ $department->name }}</td>

                                    <td class="text-right">
                                        {{ number_format($department->total_kg, 2, ',', '.') }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="2" class="text-center">
                                        Tidak ada data
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                @elseif (strtolower(auth()->user()->department->name) === 'slaughter house')
                    {{-- SLAUGHTER HOUSE --}}

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>Department</th>
                                <th class="text-right">KG</th>
                                <th class="text-right">Rp/KG</th>
                                <th class="text-right">Total Rupiah</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($departmentSummarySlaughterHouse as $department)
                                <tr>

                                    <td>
                                        {{ $department->name }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($department->total_kg, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($department->harga_per_kg, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right font-weight-bold">
                                        Rp {{ number_format($department->total_rupiah, 0, ',', '.') }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4" class="text-center">
                                        Tidak ada data
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                @else
                    {{-- SOSIS --}}

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>Department</th>
                                <th class="text-right">KG</th>
                                <th class="text-right">Rp/KG</th>
                                <th class="text-right">Total Rupiah</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($departmentSummarySosis as $department)
                                <tr>

                                    <td>
                                        {{ $department->name }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($department->total_kg, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($department->harga_per_kg, 2, ',', '.') }}
                                    </td>

                                    <td class="text-right font-weight-bold">
                                        Rp {{ number_format($department->total_rupiah, 0, ',', '.') }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4" class="text-center">
                                        Tidak ada data
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                @endif

            </div>

        </div>


        {{-- NOT INPUT DAILY ACTIVITY --}}
        <div class="row">

            <div class="col-lg-12">

                <div class="card card-danger">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Belum Input Daily Activity
                        </h4>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-striped mb-0">

                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Cost Center</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @if (strtolower(auth()->user()->department->name) === 'further processing')
                                        @forelse($notInputDailyActivityFurther as $item)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-danger">
                                                        {{ $item->department->name }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ $item->name }}
                                                </td>
                                            </tr>

                                        @empty

                                            <tr>
                                                <td colspan="2" class="text-center">

                                                    <div class="py-4">

                                                        <i class="fas fa-check-circle text-success fa-2x"></i>

                                                        <br>

                                                        <strong class="text-success">
                                                            Semua Cost Center sudah input.
                                                        </strong>

                                                    </div>

                                                </td>
                                            </tr>
                                        @endforelse
                                    @elseif (strtolower(auth()->user()->department->name) === 'slaughter house')
                                        @forelse($notInputDailyActivitySlaughterHouse as $item)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-danger">
                                                        {{ $item->department->name }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ $item->name }}
                                                </td>
                                            </tr>

                                        @empty

                                            <tr>
                                                <td colspan="2" class="text-center">

                                                    <div class="py-4">

                                                        <i class="fas fa-check-circle text-success fa-2x"></i>

                                                        <br>

                                                        <strong class="text-success">
                                                            Semua Cost Center sudah input.
                                                        </strong>

                                                    </div>

                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        @forelse($notInputDailyActivitySosis as $item)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-danger">
                                                        {{ $item->department->name }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ $item->name }}
                                                </td>
                                            </tr>

                                        @empty

                                            <tr>
                                                <td colspan="2" class="text-center">

                                                    <div class="py-4">

                                                        <i class="fas fa-check-circle text-success fa-2x"></i>

                                                        <br>

                                                        <strong class="text-success">
                                                            Semua Cost Center sudah input.
                                                        </strong>

                                                    </div>

                                                </td>
                                            </tr>
                                        @endforelse
                                    @endif

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- RECENT DAILY ACTIVITY --}}
        <div class="card">

            <div class="card-header">

                <h4>
                    <i class="fas fa-history mr-2"></i>
                    Recent Daily Activity
                </h4>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-striped">

                        <thead>

                            <tr>
                                <th>Tanggal</th>
                                <th>Department</th>
                                <th>Cost Center</th>
                                <th>Group</th>
                                <th>Input By</th>
                            </tr>

                        </thead>

                        <tbody>

                            @if (strtolower(auth()->user()->department->name) === 'further processing')
                                @forelse($recentActivitiesFurther as $activity)
                                    <tr>

                                        <td>
                                            {{ $activity->tanggal->format('d M Y') }}
                                        </td>

                                        <td>
                                            {{ optional($activity->costCenter->department)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->costCenter)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->psGroup)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->employee)->name }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center">
                                            Belum ada aktivitas.
                                        </td>
                                    </tr>
                                @endforelse
                            @elseif (strtolower(auth()->user()->department->name) === 'slaughter house')
                                @forelse($recentActivitiesSlaughterHouse as $activity)
                                    <tr>

                                        <td>
                                            {{ $activity->tanggal->format('d M Y') }}
                                        </td>

                                        <td>
                                            {{ optional($activity->costCenter->department)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->costCenter)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->psGroup)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->employee)->name }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center">
                                            Belum ada aktivitas.
                                        </td>
                                    </tr>
                                @endforelse
                            @else
                                @forelse($recentActivitiesSosis as $activity)
                                    <tr>

                                        <td>
                                            {{ $activity->tanggal->format('d M Y') }}
                                        </td>

                                        <td>
                                            {{ optional($activity->costCenter->department)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->costCenter)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->psGroup)->name }}
                                        </td>

                                        <td>
                                            {{ optional($activity->employee)->name }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center">
                                            Belum ada aktivitas.
                                        </td>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        @if (strtolower(auth()->user()->department->name) === 'further processing')

            const labels = [
                @foreach ($departmentSummaryFurther as $department)
                    '{{ $department->name }}',
                @endforeach
            ];

            const kgData = [
                @foreach ($departmentSummaryFurther as $department)
                    {{ $department->total_kg ?? 0 }},
                @endforeach
            ];

            const trendLabels = @json($trendLabels);
            const trendOutputKg = @json($trendOutputKgFurther);
        @elseif (strtolower(auth()->user()->department->name) === 'slaughter house')

            const labels = [
                @foreach ($departmentSummarySlaughterHouse as $department)
                    '{{ $department->name }}',
                @endforeach
            ];

            const kgData = [
                @foreach ($departmentSummarySlaughterHouse as $department)
                    {{ $department->total_kg ?? 0 }},
                @endforeach
            ];

            const rupiahData = [
                @foreach ($departmentSummarySlaughterHouse as $department)
                    {{ $department->total_rupiah ?? 0 }},
                @endforeach
            ];

            const trendLabels = @json($trendLabels);
            const trendOutputKg = @json($trendOutputKgSlaughterHouse);
        @else

            const labels = [
                @foreach ($departmentSummarySosis as $department)
                    '{{ $department->name }}',
                @endforeach
            ];

            const kgData = [
                @foreach ($departmentSummarySosis as $department)
                    {{ $department->total_kg ?? 0 }},
                @endforeach
            ];

            const rupiahData = [
                @foreach ($departmentSummarySosis as $department)
                    {{ $department->total_rupiah ?? 0 }},
                @endforeach
            ];

            const trendLabels = @json($trendLabels);
            const trendOutputKg = @json($trendOutputKgSosis);
        @endif


        // KG CHART
        new Chart(document.getElementById('kgChart'), {

            type: 'bar',

            data: {

                labels: labels,

                datasets: [{
                    label: 'Output KG',
                    data: kgData,
                    backgroundColor: '#4e73df'
                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {
                        display: false
                    }

                }

            }

        });


        // RUPIAH CHART
        @if (strtolower(auth()->user()->department->name) !== 'further processing')

            new Chart(document.getElementById('rupiahChart'), {

                type: 'line',

                data: {

                    labels: labels,

                    datasets: [{

                        label: 'Total Rupiah',

                        data: rupiahData,

                        borderColor: '#6777ef',

                        backgroundColor: 'rgba(103,119,239,.15)',

                        fill: true,

                        tension: .3

                    }]

                },

                options: {

                    responsive: true,

                    plugins: {

                        legend: {
                            display: false
                        }

                    }

                }

            });
        @endif


        // TREND OUTPUT
        new Chart(document.getElementById('outputChart'), {

            type: 'bar',

            data: {

                labels: trendLabels,

                datasets: [{

                    label: 'Output (kg)',

                    data: trendOutputKg,

                    backgroundColor: '#36a9e1'

                }]

            },

            options: {

                responsive: true,

                plugins: {

                    legend: {
                        display: false
                    }

                }

            }

        });
    </script>
@endpush
