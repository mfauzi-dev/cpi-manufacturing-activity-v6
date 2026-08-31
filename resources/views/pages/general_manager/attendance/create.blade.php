@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Input Attendance Manual</h1>
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
                <form method="GET" action="{{ route('general-manager.attendance.create') }}">
                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="date" class="form-control" value="{{ $date }}">
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Department</label>
                                <select id="department_id" name="department_id" class="form-control">
                                    <option value="">Semua Department</option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ $departmentId == $department->id ? 'selected' : '' }}>
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

                                    <option value="">Semua Cost Center</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Group</label>
                                <select id="ps_group_id" name="ps_group_id" class="form-control">

                                    <option value="">Semua Group</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
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

                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a href="{{ route('general-manager.attendance.create') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>
        </div>

        @if (!$departmentId)
            <div class="alert alert-warning">
                Pilih department terlebih dahulu untuk menampilkan daftar karyawan.
            </div>
        @else
            {{-- FORM ABSENSI --}}
            <form method="POST" action="{{ route('general-manager.attendance.bulk.store') }}">
                @csrf

                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="department_id" value="{{ $departmentId }}">

                <div class="card">
                    <div class="card-body table-responsive">

                        <table class="table table-bordered table-striped">

                            <thead>
                                <tr>
                                    <th>NIK</th>
                                    <th>Nama</th>
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

                                        <td>{{ $employee->nik }}</td>

                                        <td>{{ $employee->name }}</td>

                                        <td class="text-center">
                                            <input type="checkbox" class="attendance-status" data-status="hadir"
                                                {{ optional($attendance)->status == 'hadir' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox" class="attendance-status" data-status="cuti"
                                                {{ optional($attendance)->status == 'cuti' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox" class="attendance-status" data-status="izin"
                                                {{ optional($attendance)->status == 'izin' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox" class="attendance-status" data-status="sakit"
                                                {{ optional($attendance)->status == 'sakit' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox" class="attendance-status" data-status="alfa"
                                                {{ optional($attendance)->status == 'alfa' ? 'checked' : '' }}>
                                        </td>

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
                                        <td colspan="8" class="text-center">
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
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        // ====== Checkbox absensi (radio-like behaviour) ======
        document.querySelectorAll('tbody tr').forEach(row => {

            const checkboxes = row.querySelectorAll('.attendance-status');
            const hiddenInput = row.querySelector('.status-value');

            checkboxes.forEach(box => {

                box.addEventListener('change', function() {

                    if (this.checked) {

                        checkboxes.forEach(other => {

                            if (other !== this) {
                                other.checked = false;
                            }

                        });

                        hiddenInput.value = this.dataset.status;

                    } else {

                        hiddenInput.value = '';

                    }

                });

            });

        });

        document.querySelectorAll('tbody tr').forEach(row => {

            const checkboxes = row.querySelectorAll('.attendance-status');
            const hiddenInput = row.querySelector('.status-value');

            checkboxes.forEach(box => {

                box.addEventListener('change', function() {

                    if (this.checked) {

                        checkboxes.forEach(other => {
                            if (other !== this) {
                                other.checked = false;
                            }
                        });

                        hiddenInput.value = this.dataset.status;

                    } else {

                        hiddenInput.value = '';

                    }

                });

            });

        });

        const department = document.getElementById('department_id');
        const costCenter = document.getElementById('cost_center_id');
        const psGroup = document.getElementById('ps_group_id');

        const selectedCostCenter = "{{ $costCenterId }}";
        const selectedPsGroup = "{{ $psGroupId }}";

        function loadCostCenters(departmentId, selected = null) {

            psGroup.innerHTML = '<option value="">Semua Group</option>';

            if (!departmentId) {

                costCenter.innerHTML = '<option value="">Semua Cost Center</option>';
                return;

            }

            fetch(`/attendance/cost-centers/${departmentId}`)
                .then(response => response.json())
                .then(data => {

                    costCenter.innerHTML = '<option value="">Semua Cost Center</option>';

                    data.forEach(item => {

                        let option = document.createElement('option');

                        option.value = item.id;
                        option.textContent = item.name;

                        if (selected == item.id) {
                            option.selected = true;
                        }

                        costCenter.appendChild(option);

                    });

                    if (selected) {
                        loadPsGroups(selected, selectedPsGroup);
                    }

                });

        }

        function loadPsGroups(costCenterId, selected = null) {


            if (!costCenterId) {

                psGroup.innerHTML = '<option value="">Semua Group</option>';
                return;

            }

            fetch(`/attendance/ps-groups/${costCenterId}`)
                .then(response => response.json())
                .then(data => {

                    psGroup.innerHTML = '<option value="">Semua Group</option>';

                    data.forEach(item => {

                        let option = document.createElement('option');

                        option.value = item.id;
                        option.textContent = item.name;

                        if (selected == item.id) {
                            option.selected = true;
                        }

                        psGroup.appendChild(option);

                    });

                });

        }

        department.addEventListener('change', function() {

            loadCostCenters(this.value);

        });

        costCenter.addEventListener('change', function() {

            loadPsGroups(this.value);

        });

        window.addEventListener('DOMContentLoaded', function() {

            if (department.value) {

                loadCostCenters(
                    department.value,
                    selectedCostCenter
                );

            }

        });
    </script>
@endpush
