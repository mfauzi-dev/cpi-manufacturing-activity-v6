@extends('layouts.master')

@section('content')

    <div class="section-header">
        <h1>Penggajian Karyawan Tetap</h1>
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
                <h4>Filter Penggajian</h4>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('manager.penggajian-karyawan-tetap.index') }}">
                    <div class="row">

                        <div class="form-group col-md-3">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                @for ($i = now()->year - 2; $i <= now()->year + 1; $i++)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        @if (strtolower(auth()->user()->department?->name ?? '') === 'general affair')
                            <div class="form-group col-md-3">
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
                        @endif

                        <div class="form-group col-md-3">
                            <label>Cost Center</label>
                            <select name="cost_center_id" id="cost_center_id" class="form-control">
                                <option value="">Semua Cost Center</option>

                                @if (strtolower(auth()->user()->department?->name ?? '') !== 'general affair')
                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ request('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->code }} - {{ $costCenter->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group col-md-3">
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

                        <a href="{{ route('manager.penggajian-karyawan-tetap.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Data Penggajian</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>NIK</th>
                                <th>Karyawan</th>
                                <th>Department</th>
                                <th>Cost Center</th>
                                <th>Basic Salary</th>
                                <th>Overtime</th>
                                <th>Grand Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($penggajians as $penggajian)
                                <tr>
                                    <td>
                                        {{ $penggajians->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        {{ $penggajian->employee->nik ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $penggajian->employee->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $penggajian->employee->department->name ?? '-' }}
                                    </td>

                                    <td>
                                        @if ($penggajian->employee->costCenter)
                                            {{ $penggajian->employee->costCenter->code }}
                                            -
                                            {{ $penggajian->employee->costCenter->name }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        Rp {{ number_format($penggajian->basic_salary, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($penggajian->overtime_total, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($penggajian->grand_total_salary, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        Belum ada data penggajian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $penggajians->withQueryString()->links() }}
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
                    url: "{{ route('manager.penggajian-karyawan-tetap.cost-centers', ':departmentId') }}"
                        .replace(':departmentId', departmentId),

                    type: "GET",

                    success: function(data) {

                        $.each(data, function(key, value) {

                            let selected =
                                selectedCostCenter == value.id ?
                                'selected' :
                                '';

                            costCenter.append(
                                '<option value="' + value.id + '" ' + selected + '>' +
                                value.code + ' - ' + value.name +
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

            let selectedCostCenter = "{{ request('cost_center_id') }}";

            if (departmentId) {

                loadCostCenters(
                    departmentId,
                    selectedCostCenter
                );

            }

        });
    </script>
@endpush
