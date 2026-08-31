@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Rekap Daily Activity Slaughter House</h1>
    </div>

    <div class="alert alert-info">
        Department :
        <strong>{{ auth()->user()->department->name }}</strong>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session('errors'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('errors') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="section-body">

        <div class="card mb-3">
            <div class="card-body">

                <form action="{{ route('admin-production.daily-activity-slaughter-house.index') }}" method="GET">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Cost Center</label>
                                <select class="form-control" id="cost_center_id" name="cost_center_id">
                                    <option value="">Semua Cost Center</option>

                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}" @selected(request('cost_center_id') == $costCenter->id)>
                                            {{ $costCenter->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Group</label>

                                <select class="form-control" id="ps_group_id" name="ps_group_id">
                                    <option value="">Semua Group</option>
                                </select>

                            </div>
                        </div>

                        {{-- <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Product Group</label>

                                <select class="form-control" id="product_group_id" name="product_group_id">
                                    <option value="">Semua Product Group</option>
                                </select>

                            </div>
                        </div> --}}

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Line</label>

                                <select class="form-control" id="line_id" name="line_id">
                                    <option value="">Semua Line</option>

                                    @foreach ($lines as $line)
                                        <option value="{{ $line->id }}" @selected(request('line_id') == $line->id)>
                                            {{ $line->code ? $line->code . ' - ' : '' }}{{ $line->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Dari Tanggal</label>

                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date') }}">

                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>

                                <input type="date" name="end_date" class="form-control"
                                    value="{{ request('end_date') }}">

                            </div>
                        </div>

                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('admin-production.daily-activity-slaughter-house.index') }}"
                            class="btn btn-secondary">
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
                            <th>PS Group</th>
                            <th>Product Group</th>
                            <th>Line</th>
                            <th class="text-right">Total Kg</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($summaries as $key => $summary)
                            <tr>

                                <td>
                                    {{ $summaries->firstItem() + $key }}
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
                                    {{ $summary->product_group_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $summary->line_name ?? '-' }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($summary->total_kg, 2, ',', '.') }}
                                </td>

                                <td class="text-center">

                                    <a href="{{ route('admin-production.daily-activity-slaughter-house.detail', [
                                        'costCenter' => $summary->cost_center_id,
                                        'psGroup' => $summary->ps_group_id ?? '',
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
                                <td colspan="8" class="text-center">
                                    Belum ada data
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                    @if ($summaries->count())
                        <tfoot>

                            <tr>

                                <th></th>

                                <th>Total</th>

                                <th></th>

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
        $(function() {

            loadPsGroups();
            loadProductGroups();

            $('#cost_center_id').change(function() {

                loadPsGroups();
                loadProductGroups();

            });

        });


        function loadPsGroups() {

            let costCenterId = $('#cost_center_id').val();

            if (costCenterId == '') {

                $('#ps_group_id').html(
                    '<option value="">Semua Group</option>'
                );

                return;
            }

            $.get(
                '/daily-activity-slaughter-house/ps-groups/' + costCenterId,
                function(res) {

                    let html =
                        '<option value="">Semua Group</option>';

                    $.each(res, function(i, item) {

                        html += `
                        <option value="${item.id}"
                            ${item.id == "{{ request('ps_group_id') }}" ? 'selected' : ''}>
                            ${item.name}
                        </option>
                    `;

                    });

                    $('#ps_group_id').html(html);

                }
            );

        }


        function loadProductGroups() {

            let costCenterId = $('#cost_center_id').val();

            if (costCenterId == '') {

                $('#product_group_id').html(
                    '<option value="">Semua Product Group</option>'
                );

                return;
            }

            $.get(
                '/daily-activity-slaughter-house/product-groups/' + costCenterId,
                function(res) {

                    let html =
                        '<option value="">Semua Product Group</option>';

                    $.each(res, function(i, item) {

                        html += `
                        <option value="${item.id}"
                            ${item.id == "{{ request('product_group_id') }}" ? 'selected' : ''}>
                            ${item.name}
                        </option>
                    `;

                    });

                    $('#product_group_id').html(html);

                }
            );

        }
    </script>
@endpush
