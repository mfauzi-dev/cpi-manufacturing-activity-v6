@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Rekap Daily Activity</h1>
    </div>

    <div class="alert alert-info">
        Department :
        <strong>Semua Department</strong>
    </div>


    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif
    <div class="section-body">


        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6>Total KG</h6>
                        <h3>{{ number_format($grandTotalKg) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6>Total Rupiah</h6>
                        <h3>Rp {{ number_format($grandTotalRupiah, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6>Rupiah per KG Aktual</h6>
                        <h3>Rp {{ number_format($grandHargaPerKg, 2, ',', '.') }}</h3>
                    </div>
                </div>
            </div>


        </div>
        <div class="card mb-3">
            <div class="card-body">

                <form action="{{ route('admin.daily-activity.index') }}" method="GET">

                    <div class="row">

                        <div class="col-md-3">
                            <label>Department</label>
                            <select class="form-control" id="department_id" name="department_id">
                                <option value="">Semua Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>

                                        {{ $department->name }}

                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Cost Center</label>
                            <select class="form-control" id="cost_center_id" name="cost_center_id">
                                <option value="">Semua Cost Center</option>
                            </select>

                        </div>
                        <div class="col-md-2">
                            <label>Dari</label>
                            <input type="date" class="form-control" name="start_date"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label>Sampai</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label>Quick Filter</label>
                            <select name="quick_filter" class="form-control">
                                <option value="">Custom</option>
                                <option value="today" @selected(request('quick_filter') == 'today')>
                                    Hari Ini
                                </option>
                                <option value="week" @selected(request('quick_filter') == 'week')>
                                    Minggu Ini
                                </option>
                                <option value="month" @selected(request('quick_filter') == 'month')>
                                    Bulan Ini
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filter
                        </button>
                        <a href="{{ route('admin.daily-activity.index') }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Department</th>
                            <th>Cost Center</th>
                            <th class="text-right">Total Kg</th>
                            <th class="text-right">Rupiah per KG</th>
                            <th class="text-right">Total Rupiah</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($summaries as $key => $summary)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $summary->department_name }}</td>
                                <td>{{ $summary->cost_center_name }}</td>
                                <td class="text-right">{{ number_format($summary->total_kg, 0, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($summary->harga_per_kg, 2, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($summary->total_rupiah, 0, ',', '.') }}</strong></td>
                                <td class="text-center">
                                    <a href="{{ route('admin.daily-activity.detail', $summary->cost_center_id) }}"
                                        class="btn btn-info">
                                        <i class="fas fa-eye mr-2"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data</td>
                            </tr>
                        @endforelse

                    </tbody>

                    @if ($summaries->count())
                        <tfoot>
                            <tr>
                                <th></th>
                                <th>Total</th>
                                <th></th>
                                <th class="text-right">{{ number_format($grandTotalKg, 0, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format($grandHargaPerKg, 2, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format($grandTotalRupiah, 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif

                </table>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {

            loadCostCenters();

            $('#department_id').change(function() {

                loadCostCenters();

            });

        });

        function loadCostCenters() {
            let departmentId = $('#department_id').val();

            $('#cost_center_id').html(
                '<option value="">Loading...</option>'
            );

            if (departmentId == '') {

                $('#cost_center_id').html(
                    '<option value="">Semua Cost Center</option>'
                );

                return;
            }

            $.ajax({

                url: '/admin/daily-activity/cost-centers/' + departmentId,

                type: 'GET',

                success: function(res) {

                    let html = '<option value="">Semua Cost Center</option>';

                    let selected = "{{ request('cost_center_id') }}";

                    $.each(res, function(i, item) {

                        html += `
                    <option
                        value="${item.id}"
                        ${selected == item.id ? 'selected' : ''}>
                        ${item.name}
                    </option>
                `;

                    });

                    $('#cost_center_id').html(html);

                }

            });

        }
    </script>
@endpush
