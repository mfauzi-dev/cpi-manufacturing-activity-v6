@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Rekap Daily Production</h1>
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="section-body">
        <div class="card mb-3">
            <div class="card-body">

                <form action="{{ route('admin-production.daily-production.index') }}" method="GET">

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
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin-production.daily-production.index') }}" class="btn btn-secondary">Reset</a>
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
                            <th class="text-right">Total Kg</th>
                            <th class="text-right">Rupiah per KG Aktual</th>
                            <th class="text-right">Total Rupiah</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($summaries as $key => $summary)
                            <tr>
                                <td>{{ $summaries->firstItem() + $key }}</td>
                                <td>{{ $summary->department_name }}</td>
                                <td>{{ $summary->cost_center_name }}</td>
                                <td>{{ $summary->ps_group_name }}</td>
                                <td class="text-right">{{ number_format($summary->total_kg, 2, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($summary->harga_per_kg, 2, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($summary->total_rupiah, 0, ',', '.') }}</strong></td>
                                <td class="text-center">
                                    <a href="{{ route('admin-production.daily-production.detail', [
                                        'costCenter' => $summary->cost_center_id,
                                        'psGroup' => $summary->ps_group_id ?? '',
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
                                <td colspan="8" class="text-center">Belum ada data</td>
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
                                <th class="text-right">{{ number_format($grandTotalKg, 0, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format($grandHargaPerKg, 2, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format($grandTotalRupiah, 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif

                </table>

                <div class="card-footer text-right">
                    {{ $summaries->withQueryString()->links() }}
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

            $.get('/daily-production/ps-groups/' + costCenterId, function(res) {

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
