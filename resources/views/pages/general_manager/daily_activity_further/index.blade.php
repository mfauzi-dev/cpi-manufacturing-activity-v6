@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Rekap Daily Activity</h1>
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

    <div class="row">

        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Total KG RM</h6>
                    <h3>
                        {{ number_format($grandTotalKgRm, 2, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Total KG FG</h6>
                    <h3>
                        {{ number_format($grandTotalKgFg, 2, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <div class="section-body">

        <div class="card mb-3">
            <div class="card-body">

                <form action="{{ route('general-manager.daily-activity-further.index') }}" method="GET">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Department</label>

                                <select class="form-control" name="department_id">
                                    <option value="">Semua Department</option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

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

                        <a href="{{ route('general-manager.daily-activity-further.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>

            </div>
        </div>

        <div class="card">

            <div class="card-body table-responsive">
                <div class="mb-2">
                    <a href="{{ route('general-manager.daily-activity-further.export-excel', [
                        'costCenterId' => request('cost_center_id'),
                        'psGroupId' => request('ps_group_id'),
                        'line_id' => request('line_id'),
                        'date_from' => request('start_date'),
                        'date_to' => request('end_date'),
                    ]) }}"
                        class="btn btn-primary">
                        <i class="fas fa-file-excel mr-2"></i>
                        Excel Detail
                    </a>
                </div>

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Department</th>
                            <th>Cost Center</th>
                            <th>PS Group</th>
                            <th>Line</th>
                            <th class="text-right">Total Kg RM</th>
                            <th class="text-right">Total Kg FG</th>
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
                                    {{ $summary->line_name ?? '-' }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($summary->total_kg_rm, 2, ',', '.') }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($summary->total_kg_fg, 2, ',', '.') }}
                                </td>

                                <td class="text-center">

                                    <a href="{{ route('general-manager.daily-activity-further.detail', [
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

                                <th class="text-right">
                                    {{ number_format($grandTotalKgRm, 2, ',', '.') }}
                                </th>

                                <th class="text-right">
                                    {{ number_format($grandTotalKgFg, 2, ',', '.') }}
                                </th>

                                <th></th>

                            </tr>
                        </tfoot>
                    @endif

                </table>

                <div class="mt-3">
                    {{ $summaries->appends(request()->query())->links() }}
                </div>

            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {

            loadPsGroups();

            $('#cost_center_id').change(function() {
                loadPsGroups();
            });

        });

        function loadPsGroups() {

            let costCenterId = $('#cost_center_id').val();

            if (costCenterId == '') {
                $('#ps_group_id').html('<option value="">Semua Group</option>');
                return;
            }

            $.get('/daily-activity-further/ps-groups/' + costCenterId, function(res) {

                let html = '<option value="">Semua Group</option>';

                $.each(res, function(i, item) {

                    html += `
                    <option value="${item.id}" ${item.id == "{{ request('ps_group_id') }}" ? 'selected' : ''}>
                        ${item.name}
                    </option>
                `;

                });

                $('#ps_group_id').html(html);
            });
        }
    </script>
@endpush
