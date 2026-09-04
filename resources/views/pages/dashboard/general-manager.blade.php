@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Dashboard</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <form method="GET">
                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date"
                                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date"
                                    value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" id="department_id" class="form-control">

                                    <option value="">Semua Department</option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Cost Center</label>

                                <select id="cost_center_id" name="cost_center_id" class="form-control">

                                    <option value="">
                                        Semua Cost Center
                                    </option>

                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>


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


        <div class="row">

            {{-- TOTAL KG SAUSAGE BORONGAN --}}
            <div class="col-lg-4 col-md-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-weight"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total KG Sausage Borongan</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($totalKgBorongan, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOTAL KG SAUSAGE HARIAN --}}
            <div class="col-lg-4 col-md-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-success">
                        <i class="fas fa-weight"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total KG Sausage Harian</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($totalKgHarian, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOTAL KG FURTHER PROCESSING --}}
            <div class="col-lg-4 col-md-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-warning">
                        <i class="fas fa-weight"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total KG Further Processing</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($totalKgFurther, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>


        {{-- CARD BAWAH --}}
        <div class="row justify-content-center">

            {{-- TOTAL KG SLAUGHTER HOUSE --}}
            <div class="col-lg-4 col-md-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-danger">
                        <i class="fas fa-weight"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total KG Slaughter House</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($totalKgSlaughterHouse, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOTAL RUPIAH --}}
            <div class="col-lg-4 col-md-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-info">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Rupiah</h4>
                        </div>
                        <div class="card-body">
                            Rp {{ number_format($totalRupiah, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="row">

            <div class="col-lg-6">
                <div class="card">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-chart-bar mr-2"></i>
                            Output KG per {{ $chartMode === 'cost_center' ? 'Cost Center' : 'Department' }}
                        </h4>
                    </div>

                    <div class="card-body">
                        <canvas id="kgChart" height="180"></canvas>
                    </div>

                </div>
            </div>


            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-chart-line mr-2"></i>
                            Total Rupiah per {{ $chartMode === 'cost_center' ? 'Cost Center' : 'Department' }}
                        </h4>
                    </div>

                    <div class="card-body">
                        <canvas id="rupiahChart" height="180"></canvas>
                    </div>

                </div>

            </div>

        </div>


        <div class="row">

            <div class="col-lg-6">
                <div class="card">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-chart-area mr-2"></i>
                            Tren Output Harian (kg)
                            @if ($chartMode === 'cost_center')
                                <small class="text-muted d-block">per Cost Center</small>
                            @endif
                        </h4>
                    </div>

                    <div class="card-body">
                        <canvas id="outputChart" height="180"></canvas>
                    </div>

                </div>
            </div>


            <div class="col-lg-6">
                <div class="card">

                    <div class="card-header">
                        <h4>
                            <i class="fas fa-chart-bar mr-2"></i>
                            Rupiah Per KG Aktual
                        </h4>
                    </div>

                    <div class="card-body">
                        <canvas id="hargaPerKgChart"></canvas>
                    </div>

                </div>
            </div>

        </div>


        <div class="row">

            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">

                        <h4>
                            Progress Attendance
                        </h4>

                        <small class="text-muted">

                            @if (request('department_id'))
                                {{ optional($departments->firstWhere('id', request('department_id')))->name }}
                            @else
                                Semua Department
                            @endif

                        </small>

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


            <div class="col-lg-6">

                <div class="card">

                    <div class="card-header">

                        <h4>
                            Progress Daily Activity
                        </h4>

                        <small class="text-muted">
                            Semua department
                        </small>

                    </div>


                    <div class="card-body">

                        <div class="mb-2 d-flex justify-content-between">

                            <span>
                                {{ number_format($dailyActivityProgress, 1) }}%
                            </span>

                        </div>


                        <div class="progress" style="height:20px;">

                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width:{{ $dailyActivityProgress }}%">

                                {{ number_format($dailyActivityProgress, 1) }}%

                            </div>

                        </div>


                        <div class="mt-4">

                            <table class="table table-sm">

                                <tr>

                                    <td>Total KG</td>

                                    <td class="text-right">
                                        {{ number_format($totalKg, 2, ',', '.') }}
                                    </td>

                                </tr>

                                <tr>

                                    <td>Total Rupiah</td>

                                    <td class="text-right font-weight-bold">
                                        Rp {{ number_format($totalRupiah, 0, ',', '.') }}
                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="card">

            <div class="card-header">
                <h4>Summary {{ $chartMode === 'cost_center' ? 'Cost Center' : 'Department' }}</h4>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th>{{ $chartMode === 'cost_center' ? 'Cost Center' : 'Department' }}</th>

                            <th class="text-right">
                                KG
                            </th>

                            <th class="text-right">
                                Rp/KG
                            </th>

                            <th class="text-right">
                                Total Rupiah
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($summaryTable as $item)
                            <tr>

                                <td>
                                    {{ $item->name }}
                                </td>


                                <td class="text-right">

                                    {{ number_format($item->total_kg ?? 0, 2, ',', '.') }}

                                </td>


                                <td class="text-right">

                                    @if (is_null($item->harga_per_kg))
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @else
                                        Rp
                                        {{ number_format($item->harga_per_kg, 2, ',', '.') }}
                                    @endif

                                </td>


                                <td class="text-right font-weight-bold">

                                    @if (is_null($item->total_rupiah))
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @else
                                        Rp
                                        {{ number_format($item->total_rupiah, 0, ',', '.') }}
                                    @endif

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

            </div>

        </div>


        <div class="row">

            <div class="col-lg-12">

                <div class="card card-danger">

                    <div class="card-header">

                        <h4>

                            <i class="fas fa-exclamation-circle mr-2"></i>

                            Belum Input Daily Activity

                        </h4>

                        <small class="text-muted">
                            Semua department
                        </small>

                    </div>


                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-striped mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            Department
                                        </th>

                                        <th>
                                            Cost Center
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse($notInputDailyActivity as $item)
                                        <tr>

                                            <td>

                                                <span class="badge badge-danger">

                                                    {{ optional($item->department)->name }}

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

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>


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

                            @forelse($recentActivities as $activity)
                                <tr>

                                    <td>
                                        {{ optional($activity->tanggal)->format('d M Y') }}
                                    </td>

                                    <td>
                                        {{ optional(optional($activity->costCenter)->department)->name }}
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
        const department =
            document.getElementById('department_id');

        const costCenter =
            document.getElementById('cost_center_id');

        const selectedCostCenter =
            "{{ request('cost_center_id') }}";


        function loadCostCenters(departmentId, selected = null) {

            if (!departmentId) {

                costCenter.innerHTML =
                    '<option value="">Semua Cost Center</option>';

                return;
            }


            fetch(`/dashboard/cost-centers/${departmentId}`)

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Gagal mengambil data Cost Center'
                        );

                    }

                    return response.json();

                })


                .then(data => {

                    costCenter.innerHTML =
                        '<option value="">Semua Cost Center</option>';


                    data.forEach(item => {

                        const option =
                            document.createElement('option');

                        option.value = item.id;

                        option.textContent = item.name;


                        if (selected &&
                            selected == item.id) {

                            option.selected = true;

                        }


                        costCenter.appendChild(option);

                    });

                })


                .catch(error => {

                    console.error(error);

                    costCenter.innerHTML =
                        '<option value="">Gagal memuat Cost Center</option>';

                });

        }


        department.addEventListener(
            'change',
            function() {

                loadCostCenters(this.value);

            }
        );


        window.addEventListener(
            'DOMContentLoaded',
            function() {

                if (department.value) {

                    loadCostCenters(
                        department.value,
                        selectedCostCenter
                    );

                }

            }
        );
    </script>

    <script>
        const chartColors = [
            '#4e73df',
            '#1cc88a',
            '#f6c23e',
            '#9B8AFB',
            '#36b9cc',
            '#858796'
        ];

        const chartLabels = @json($chartLabels);
        const chartDatasets = @json($chartDatasets);

        const summaryTable = @json($summaryTable);

        const trendLabels = @json($trendLabels);
        const trendDatasets = @json($trendDatasets);


        const kgChartElement =
            document.getElementById('kgChart');


        if (kgChartElement) {

            new Chart(
                kgChartElement, {
                    type: 'bar',

                    data: {

                        labels: chartLabels,

                        datasets: chartDatasets.map((ds, i) => ({

                            label: ds.label,

                            data: ds.data,

                            backgroundColor: chartColors[i % chartColors.length],

                            barPercentage: 0.85,

                            categoryPercentage: 0.75


                        }))

                    },

                    options: {

                        responsive: true,

                        scales: {

                            y: {

                                beginAtZero: true

                            }

                        }

                    }

                }
            );

        }


        const rupiahLabels = summaryTable
            .filter(item => item.total_rupiah !== null)
            .map(item => item.name);

        const rupiahData = summaryTable
            .filter(item => item.total_rupiah !== null)
            .map(item => item.total_rupiah);


        const rupiahChartElement =
            document.getElementById('rupiahChart');


        if (rupiahChartElement) {

            new Chart(
                rupiahChartElement, {
                    type: 'line',

                    data: {

                        labels: rupiahLabels,

                        datasets: [{

                            label: 'Total Rupiah',

                            data: rupiahData,

                            borderColor: '#6777ef',

                            backgroundColor: 'rgba(103,119,239,.15)',

                            fill: true,

                            tension: 0.3

                        }]

                    },

                    options: {

                        responsive: true,

                        plugins: {

                            legend: {

                                display: false

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback: function(value) {

                                        return 'Rp ' +
                                            new Intl.NumberFormat(
                                                'id-ID'
                                            ).format(value);

                                    }

                                }

                            }

                        }

                    }

                }
            );

        }


        const outputChartElement =
            document.getElementById('outputChart');

        if (outputChartElement) {
            new Chart(
                outputChartElement, {
                    type: 'bar',

                    data: {
                        labels: trendLabels,

                        datasets: trendDatasets.map((ds, i) => ({

                            label: ds.label === 'Karyawan Borongan' ?
                                'Sausage - Karyawan Borongan' : ds.label === 'Karyawan Harian' ?
                                'Sausage - Karyawan Harian' : ds.label,

                            data: ds.data,

                            backgroundColor: chartColors[i % chartColors.length],

                            barPercentage: 0.85,
                            categoryPercentage: 0.75
                        }))
                    },

                    options: {
                        responsive: true,

                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },

                        plugins: {
                            legend: {
                                display: true
                            }
                        }
                    }
                }
            );
        }


        const hargaPerKgLabels = summaryTable
            .filter(item => item.harga_per_kg !== null && item.harga_per_kg > 0)
            .map(item => item.name);

        const hargaPerKgData = summaryTable
            .filter(item => item.harga_per_kg !== null && item.harga_per_kg > 0)
            .map(item => item.harga_per_kg);


        const hargaPerKgChartElement =
            document.getElementById('hargaPerKgChart');


        if (hargaPerKgChartElement) {

            new Chart(
                hargaPerKgChartElement, {
                    type: 'bar',

                    data: {

                        labels: hargaPerKgLabels,

                        datasets: [{

                            label: 'Rupiah / KG Aktual',

                            data: hargaPerKgData,

                            backgroundColor: '#36b9cc'

                        }]

                    },

                    options: {

                        responsive: true,

                        plugins: {

                            legend: {

                                display: false

                            },

                            tooltip: {

                                callbacks: {

                                    label: function(context) {

                                        return 'Rp ' +

                                            new Intl.NumberFormat(
                                                'id-ID'
                                            ).format(context.raw) +

                                            ' / KG';

                                    }

                                }

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback: function(value) {

                                        return 'Rp ' +

                                            new Intl.NumberFormat(
                                                'id-ID'
                                            ).format(value);

                                    }

                                }

                            }

                        }

                    }

                }
            );

        }
    </script>
@endpush
