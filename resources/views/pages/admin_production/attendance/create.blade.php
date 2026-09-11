@extends('layouts.master')

@push('addon-style')
    <style>
        .attendance-table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .attendance-table {
            width: max-content;
            min-width: 100%;
            table-layout: auto;
            white-space: nowrap;
        }

        .attendance-table th,
        .attendance-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .attendance-table th {
            min-width: 100px;
        }

        .attendance-table th:nth-child(1),
        .attendance-table td:nth-child(1) {
            min-width: 120px;
        }

        .attendance-table th:nth-child(2),
        .attendance-table td:nth-child(2) {
            min-width: 180px;
        }

        .attendance-table th:nth-child(3),
        .attendance-table td:nth-child(3) {
            min-width: 180px;
        }

        .attendance-table th:nth-child(4),
        .attendance-table td:nth-child(4) {
            min-width: 120px;
        }

        .attendance-table th:nth-child(10),
        .attendance-table td:nth-child(10) {
            min-width: 150px;
        }

        .attendance-table input.form-control,
        .attendance-table select.form-control {
            min-width: 120px;
        }

        .attendance-table input[type="text"] {
            min-width: 200px;
        }

        .attendance-table input[type="number"] {
            min-width: 100px;
        }

        .attendance-table select {
            min-width: 150px;
        }

        .attendance-table .attendance-status {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="section-header">
        <h1>Input Attendance Manual</h1>
    </div>

    <div class="alert alert-info">
        Department :
        <strong>{{ auth()->user()->department->name }}</strong>
    </div>

    <div class="section-body">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('admin-production.attendance.create') }}">

                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal</label>

                                <input type="date" name="date" class="form-control"
                                    value="{{ request('date', $date) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status Karyawan</label>

                                <select name="employee_status" class="form-control">

                                    <option value="">Semua</option>

                                    <option value="borongan"
                                        {{ request('employee_status') == 'borongan' ? 'selected' : '' }}>
                                        Borongan
                                    </option>

                                    <option value="harian" {{ request('employee_status') == 'harian' ? 'selected' : '' }}>
                                        Harian
                                    </option>

                                    <option value="cpi" {{ request('employee_status') == 'cpi' ? 'selected' : '' }}>
                                        CPI
                                    </option>

                                    <option value="harian_kontrak"
                                        {{ request('employee_status') == 'harian_kontrak' ? 'selected' : '' }}>
                                        Harian Kontrak
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Cost Center</label>

                                <select name="cost_center_id" id="cost_center_id" class="form-control">

                                    <option value="">
                                        Semua Cost Center
                                    </option>

                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ $costCenterId == $costCenter->id ? 'selected' : '' }}>

                                            {{ $costCenter->name }}

                                        </option>
                                    @endforeach

                                </select>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">

                                <label>PS Group</label>

                                <select name="ps_group_id" id="ps_group_id" class="form-control">

                                    <option value="">
                                        Semua PS Group
                                    </option>

                                </select>

                            </div>
                        </div>

                        <div class="col-md-3 mb-2">

                            <div class="form-group">

                                <label>Nama Karyawan</label>

                                <input type="text" name="search" class="form-control" placeholder="Cari NIK / Nama"
                                    value="{{ request('search') }}">

                            </div>

                        </div>

                    </div>

                    <div class="mt-2">

                        <button type="submit" class="btn btn-primary">

                            Terapkan Filter

                        </button>

                        <a href="{{ route('admin-production.attendance.create') }}" class="btn btn-secondary">

                            Reset

                        </a>

                    </div>

                </form>

            </div>
        </div>


        {{-- FORM ABSENSI --}}
        <form method="POST" action="{{ route('admin-production.attendance.bulk.store') }}">

            @csrf

            <input type="hidden" name="date" value="{{ request('date', $date) }}">


            <div class="card">

                <div class="card-body attendance-table-wrapper">

                    <table class="table table-bordered table-striped attendance-table">

                        <thead>

                            <tr>

                                <th>NIK</th>

                                <th>Nama</th>

                                @if (strtolower(auth()->user()->department->name) === 'further processing')
                                    <th>Line</th>
                                @endif

                                <th>Shift</th>

                                <th>Jumlah HK</th>

                                <th>Hadir</th>

                                <th>Cuti</th>

                                <th>Izin</th>

                                <th>Sakit</th>

                                <th>Alpa</th>

                                <th>Keterangan Izin</th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($employees as $employee)
                                @php
                                    $attendance = $employee->attendances->first();
                                @endphp

                                <tr>

                                    {{-- NIK --}}
                                    <td>
                                        {{ $employee->nik }}
                                    </td>


                                    {{-- NAMA --}}
                                    <td>
                                        {{ $employee->name }}
                                    </td>

                                    {{-- LINE --}}
                                    @if (strtolower(auth()->user()->department->name) === 'further processing')
                                        <td>

                                            <select name="employees[{{ $employee->id }}][line_id]" class="form-control">

                                                <option value="">
                                                    Pilih Line
                                                </option>

                                                @foreach ($lineList as $line)
                                                    <option value="{{ $line->id }}"
                                                        {{ optional($attendance)->line_id == $line->id ? 'selected' : '' }}>

                                                        {{ $line->name }}

                                                    </option>
                                                @endforeach

                                            </select>

                                        </td>
                                    @endif

                                    {{-- SHIFT --}}
                                    <td>

                                        <select name="employees[{{ $employee->id }}][shift_id]" class="form-control">

                                            <option value="">
                                                Pilih Shift
                                            </option>

                                            @foreach ($shifts as $shift)
                                                <option value="{{ $shift->id }}"
                                                    {{ old('employees.' . $employee->id . '.shift_id', optional($attendance)->shift_id) == $shift->id
                                                        ? 'selected'
                                                        : '' }}>

                                                    {{ $shift->name }}

                                                    @if ($shift->jam_masuk && $shift->jam_keluar)
                                                        ({{ \Carbon\Carbon::parse($shift->jam_masuk)->format('H:i') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($shift->jam_keluar)->format('H:i') }})
                                                    @endif

                                                </option>
                                            @endforeach

                                        </select>

                                    </td>


                                    {{-- JUMLAH HK --}}
                                    <td>

                                        <input type="number" name="employees[{{ $employee->id }}][jumlah_hk]"
                                            class="form-control" min="0" max="31" step="0.01"
                                            value="{{ old('employees.' . $employee->id . '.jumlah_hk', optional($attendance)->jumlah_hk) }}"
                                            placeholder="HK">

                                    </td>


                                    {{-- HADIR --}}
                                    <td class="text-center">

                                        <input type="checkbox" class="attendance-status" data-status="hadir"
                                            {{ optional($attendance)->status == 'hadir' ? 'checked' : '' }}>

                                    </td>


                                    {{-- CUTI --}}
                                    <td class="text-center">

                                        <input type="checkbox" class="attendance-status" data-status="cuti"
                                            {{ optional($attendance)->status == 'cuti' ? 'checked' : '' }}>

                                    </td>


                                    {{-- IZIN --}}
                                    <td class="text-center">

                                        <input type="checkbox" class="attendance-status" data-status="izin"
                                            {{ optional($attendance)->status == 'izin' ? 'checked' : '' }}>

                                    </td>


                                    {{-- SAKIT --}}
                                    <td class="text-center">

                                        <input type="checkbox" class="attendance-status" data-status="sakit"
                                            {{ optional($attendance)->status == 'sakit' ? 'checked' : '' }}>

                                    </td>


                                    {{-- ALPA --}}
                                    <td class="text-center">

                                        <input type="checkbox" class="attendance-status" data-status="alfa"
                                            {{ optional($attendance)->status == 'alfa' ? 'checked' : '' }}>

                                    </td>


                                    {{-- KETERANGAN --}}
                                    <td>

                                        <input type="hidden" class="status-value"
                                            name="employees[{{ $employee->id }}][status]"
                                            value="{{ optional($attendance)->status }}">


                                        <input type="text" name="employees[{{ $employee->id }}][keterangan_izin]"
                                            class="form-control"
                                            value="{{ old('employees.' . $employee->id . '.keterangan_izin', optional($attendance)->keterangan_izin) }}"
                                            placeholder="Keterangan">

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="{{ strtolower(auth()->user()->department->name) === 'further processing' ? 11 : 10 }}"
                                        class="text-center">

                                        Tidak ada data karyawan

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>


                    <div class="card-footer text-right">

                        {{ $employees->withQueryString()->links() }}

                    </div>

                </div>


                @if ($employees->count())
                    <div class="card-footer text-right">

                        <button type="submit" class="btn btn-primary">

                            <i class="fas fa-save"></i>

                            Simpan Attendance

                        </button>

                    </div>
                @endif

            </div>

        </form>

    </div>


@endsection

@push('scripts')
    <script>
        /*
                                                    |--------------------------------------------------------------------------
                                                    | Attendance Status
                                                    |--------------------------------------------------------------------------
                                                    */

        document.querySelectorAll('tbody tr').forEach(row => {

            const checkboxes =
                row.querySelectorAll('.attendance-status');

            const hiddenInput =
                row.querySelector('.status-value');


            checkboxes.forEach(box => {

                box.addEventListener('change', function() {

                    if (this.checked) {

                        checkboxes.forEach(other => {

                            if (other !== this) {
                                other.checked = false;
                            }

                        });

                        hiddenInput.value =
                            this.dataset.status;

                    } else {

                        hiddenInput.value = '';

                    }

                });

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Cost Center -> PS Group
        |--------------------------------------------------------------------------
        */

        const costCenter =
            document.getElementById('cost_center_id');

        const psGroup =
            document.getElementById('ps_group_id');

        const selectedPsGroup =
            "{{ $psGroupId }}";


        function loadPsGroups(costCenterId, selected = null) {

            psGroup.innerHTML =
                '<option value="">Loading...</option>';


            if (!costCenterId) {

                psGroup.innerHTML =
                    '<option value="">Semua PS Group</option>';

                return;

            }


            fetch(`/attendance/ps-groups/${costCenterId}`)

                .then(response => response.json())

                .then(data => {

                    psGroup.innerHTML =
                        '<option value="">Semua PS Group</option>';


                    data.forEach(item => {

                        let option =
                            document.createElement('option');

                        option.value =
                            item.id;

                        option.textContent =
                            item.name;


                        if (selected == item.id) {

                            option.selected = true;

                        }


                        psGroup.appendChild(option);

                    });

                });

        }


        costCenter.addEventListener('change', function() {

            loadPsGroups(this.value);

        });


        window.addEventListener('DOMContentLoaded', function() {

            if (costCenter.value) {

                loadPsGroups(
                    costCenter.value,
                    selectedPsGroup
                );

            }

        });
    </script>
@endpush
