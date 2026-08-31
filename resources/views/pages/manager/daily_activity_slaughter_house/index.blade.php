@extends('layouts.master')

@section('content')
    <div class="section-header">

        <h1>Rekap Daily Activity Slaughter House</h1>

    </div>

    <div class="alert alert-info">

        Department :

        <strong>{{ $managerDepartment->name }}</strong>

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

        {{-- SUMMARY --}}

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-body text-center">

                        <h6>Total KG</h6>

                        <h3>

                            {{ number_format($grandTotalKg, 2, ',', '.') }}

                        </h3>

                    </div>

                </div>

            </div>

        </div>

        {{-- FILTER --}}

        <div class="card mb-3">

            <div class="card-body">

                <form action="{{ route('manager.daily-activity-slaughter-house.index') }}" method="GET">

                    <div class="row">

                        {{-- COST CENTER --}}

                        <div class="col-md-4 mb-3">

                            <div class="form-group">

                                <label>Cost Center</label>

                                <select class="form-control" id="cost_center_id" name="cost_center_id">

                                    <option value="">

                                        Semua Cost Center

                                    </option>

                                </select>

                            </div>

                        </div>

                        {{-- PS GROUP --}}

                        <div class="col-md-4 mb-3">

                            <div class="form-group">

                                <label>Group</label>

                                <select class="form-control" id="ps_group_id" name="ps_group_id">

                                    <option value="">

                                        Semua Group

                                    </option>

                                </select>

                            </div>

                        </div>

                        {{-- START DATE --}}

                        <div class="col-md-4 mb-3">

                            <div class="form-group">

                                <label>Dari Tanggal</label>

                                <input type="date" class="form-control" name="start_date"
                                    value="{{ request('start_date') }}">

                            </div>

                        </div>

                        {{-- END DATE --}}

                        <div class="col-md-4 mb-3">

                            <div class="form-group">

                                <label>Sampai Tanggal</label>

                                <input type="date" class="form-control" name="end_date"
                                    value="{{ request('end_date') }}">

                            </div>

                        </div>

                        {{-- QUICK FILTER --}}

                        <div class="col-md-4 mb-3">

                            <div class="form-group">

                                <label>Quick Filter</label>

                                <select name="quick_filter" class="form-control">

                                    <option value="">

                                        Custom

                                    </option>

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

                    </div>

                    <div class="mt-3">

                        <button type="submit" class="btn btn-primary">

                            <i class="fas fa-search"></i>

                            Filter

                        </button>

                        <a href="{{ route('manager.daily-activity-slaughter-house.index') }}" class="btn btn-secondary">

                            Reset

                        </a>

                    </div>

                </form>

            </div>

        </div>

        {{-- TABLE --}}

        <div class="card">

            <div class="card-body table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Department</th>

                            <th>Cost Center</th>

                            <th>PS Group</th>

                            <th>Line</th>

                            <th class="text-right">Total Kg</th>

                            <th width="100">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($summaries as $key => $summary)
                            <tr>

                                <td>

                                    {{ $key + 1 }}

                                </td>

                                <td>

                                    {{ $summary->department_name }}

                                </td>

                                <td>

                                    {{ $summary->cost_center_name }}

                                </td>

                                <td>

                                    {{ $summary->ps_group_name }}

                                </td>

                                <td>

                                    {{ $summary->line_name ?? '-' }}

                                </td>

                                <td class="text-right">

                                    {{ number_format($summary->total_kg, 2, ',', '.') }}

                                </td>

                                <td class="text-center">

                                    <a href="{{ route('manager.daily-activity-slaughter-house.detail', [
                                        'costCenter' => $summary->cost_center_id,
                                        'psGroup' => $summary->ps_group_id,
                                        'lineId' => $summary->line_id ?? '',
                                        'date_from' => $dateFrom,
                                        'date_to' => $dateTo,
                                    ]) }}"
                                        class="btn btn-info">

                                        <i class="fas fa-eye mr-2"></i>

                                        Detail

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="text-center">

                                    Belum ada data

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                    {{-- TOTAL --}}

                    @if ($summaries->count())
                        <tfoot>

                            <tr>

                                <th></th>

                                <th>Total</th>

                                <th></th>

                                <th></th>

                                <th></th>

                                <th class="text-right">

                                    {{ number_format($grandTotalKg, 2, ',', '.') }}

                                </th>

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
        // Department manager tetap/fixed dari server

        const managerDepartmentId = "{{ $managerDepartment->id }}";

        const costCentersUrlTemplate =
            "{{ route('daily-activity-slaughter-house.cost-centers', [
                'departmentId' => '__ID__',
            ]) }}";

        const psGroupsUrlTemplate =
            "{{ route('daily-activity-slaughter-house.ps-groups', [
                'costCenterId' => '__ID__',
            ]) }}";


        $(function() {

            // Load cost center berdasarkan department manager

            loadCostCenters();

            $('#cost_center_id').change(function() {

                loadPsGroups();

            });

        });


        function loadCostCenters() {

            $('#cost_center_id').html(

                '<option value="">Loading...</option>'

            );

            $.ajax({

                url: costCentersUrlTemplate.replace(

                    '__ID__',

                    managerDepartmentId

                ),

                type: 'GET',

                success: function(res) {

                    let html =

                        '<option value="">Semua Cost Center</option>';

                    let selected =

                        "{{ request('cost_center_id') }}";


                    $.each(res, function(i, item) {

                        html += `

                    <option value="${item.id}"

                        ${selected == item.id ? 'selected' : ''}>

                        ${item.name}

                    </option>

                `;

                    });


                    $('#cost_center_id').html(html);

                    // Load PS Group

                    loadPsGroups();

                }

            });

        }


        function loadPsGroups() {

            let costCenterId =

                $('#cost_center_id').val();


            $('#ps_group_id').html(

                '<option value="">Loading...</option>'

            );


            if (costCenterId == '') {

                $('#ps_group_id').html(

                    '<option value="">Semua Group</option>'

                );

                return;

            }


            $.ajax({

                url: psGroupsUrlTemplate.replace(

                    '__ID__',

                    costCenterId

                ),

                type: 'GET',

                success: function(res) {

                    let html =

                        '<option value="">Semua Group</option>';

                    let selected =

                        "{{ request('ps_group_id') }}";


                    $.each(res, function(i, item) {

                        html += `

                    <option value="${item.id}"

                        ${selected == item.id ? 'selected' : ''}>

                        ${item.name}

                    </option>

                `;

                    });


                    $('#ps_group_id').html(html);

                }

            });

        }
    </script>
@endpush
