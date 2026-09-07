@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Approval Overtime</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Filter Overtime</h4>
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('general-manager.overtime.index') }}">

                    <div class="row">

                        <div class="form-group col-md-3">

                            <label>Department</label>

                            <select name="department_id" id="department_id" class="form-control">

                                <option value="">
                                    Semua Department
                                </option>

                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ request('department_id') == $department->id ? 'selected' : '' }}>

                                        {{ $department->name }}

                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="form-group col-md-3">

                            <label>Cost Center</label>

                            <select name="cost_center_id" id="cost_center_id" class="form-control">

                                <option value="">
                                    Semua Cost Center
                                </option>

                            </select>

                        </div>

                        <div class="form-group col-md-3">

                            <label>Dari Tanggal</label>

                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">

                        </div>

                        <div class="form-group col-md-3">

                            <label>Sampai Tanggal</label>

                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">

                        </div>

                        <div class="form-group col-md-3">

                            <label>Status</label>

                            <select name="status" class="form-control">

                                <option value="">
                                    Semua Status
                                </option>

                                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>
                                    PENDING
                                </option>

                                <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>
                                    APPROVED
                                </option>

                                <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>
                                    REJECTED
                                </option>

                            </select>

                        </div>

                        <div class="form-group col-md-6">

                            <label>Search</label>

                            <input type="text" name="search" class="form-control"
                                placeholder="Cari NIK atau nama karyawan..." value="{{ request('search') }}">

                        </div>

                    </div>

                    <div class="mt-2">

                        <button type="submit" class="btn btn-primary mr-2">

                            <i class="fas fa-search"></i>
                            Filter

                        </button>

                        <a href="{{ route('general-manager.overtime.index') }}" class="btn btn-secondary">

                            <i class="fas fa-sync"></i>
                            Reset

                        </a>

                    </div>

                </form>

            </div>

        </div>

        <div class="card">

            <div class="card-header">

                <h4>Data Overtime</h4>

                <div class="card-header-action">

                    <span class="badge badge-primary">
                        {{ $overtimes->total() }} Data
                    </span>

                </div>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>

                            <tr>

                                <th width="50">
                                    No
                                </th>

                                <th>
                                    Tanggal
                                </th>

                                <th>
                                    NIK
                                </th>

                                <th>
                                    Karyawan
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Cost Center
                                </th>

                                <th>
                                    Jam
                                </th>

                                <th>
                                    Actual
                                </th>

                                <th>
                                    Konversi
                                </th>

                                <th>
                                    Rate
                                </th>

                                <th>
                                    Total Overtime
                                </th>

                                <th>
                                    Status
                                </th>

                                <th width="120">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($overtimes as $overtime)
                                <tr>

                                    <td>
                                        {{ $overtimes->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($overtime->date)->format('d-m-Y') }}
                                    </td>

                                    <td>
                                        {{ $overtime->employee->nik ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $overtime->employee->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $overtime->employee->department->name ?? '-' }}
                                    </td>

                                    <td>

                                        @if ($overtime->employee->costCenter)
                                            {{ $overtime->employee->costCenter->code }}
                                            -
                                            {{ $overtime->employee->costCenter->name }}
                                        @else
                                            -
                                        @endif

                                    </td>

                                    <td>

                                        {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}

                                    </td>

                                    <td>

                                        {{ number_format((float) $overtime->total_hours_actual, 2, ',', '.') }}
                                        jam

                                    </td>

                                    <td>

                                        {{ number_format((float) $overtime->total_hours_konversi, 2, ',', '.') }}
                                        jam

                                    </td>

                                    <td>

                                        Rp
                                        {{ number_format((float) $overtime->hourly_rate, 0, ',', '.') }}

                                    </td>

                                    <td>

                                        <strong>

                                            Rp
                                            {{ number_format((float) $overtime->overtime_amount, 0, ',', '.') }}

                                        </strong>

                                    </td>

                                    <td>

                                        @if ($overtime->status === 'PENDING')
                                            <span class="badge badge-warning">
                                                PENDING
                                            </span>
                                        @elseif ($overtime->status === 'APPROVED')
                                            <span class="badge badge-success">
                                                APPROVED
                                            </span>
                                        @elseif ($overtime->status === 'REJECTED')
                                            <span class="badge badge-danger">
                                                REJECTED
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                {{ ucfirst($overtime->status ?? '-') }}
                                            </span>
                                        @endif

                                    </td>

                                    <td style="white-space: nowrap;">

                                        <a href="{{ route('general-manager.overtime.show', $overtime->id) }}"
                                            class="btn btn-info btn-sm" title="Detail">

                                            <i class="fas fa-eye"></i>
                                            Lihat Detail

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="13" class="text-center">

                                        Belum ada data overtime.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">

                    {{ $overtimes->withQueryString()->links() }}

                </div>

            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function loadCostCenters(departmentId, selectedCostCenter = '') {

                let costCenter = $('#cost_center_id');

                costCenter.empty();

                costCenter.append(
                    '<option value="">Semua Cost Center</option>'
                );

                if (!departmentId) {
                    return;
                }

                $.ajax({

                    url: "{{ route('overtime.cost-centers', ':departmentId') }}"
                        .replace(':departmentId', departmentId),

                    type: "GET",

                    success: function(data) {

                        $.each(data, function(key, value) {

                            let selected =
                                String(selectedCostCenter) === String(value.id) ?
                                'selected' :
                                '';

                            costCenter.append(
                                '<option value="' +
                                value.id +
                                '" ' +
                                selected +
                                '>' +
                                value.code +
                                ' - ' +
                                value.name +
                                '</option>'
                            );

                        });

                    },

                    error: function() {

                        alert('Gagal mengambil data Cost Center.');

                    }

                });

            }

            $('#department_id').on('change', function() {

                let departmentId = $(this).val();

                loadCostCenters(departmentId);

            });

            let departmentId = $('#department_id').val();

            let selectedCostCenter =
                "{{ request('cost_center_id') }}";

            if (departmentId) {

                loadCostCenters(
                    departmentId,
                    selectedCostCenter
                );

            }

        });
    </script>
@endpush
