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
                <form method="GET" action="{{ route('admin.attendance.create') }}">
                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <label>Department</label>
                            <select name="department_id" id="department_id" class="form-control" required>
                                <option value="">Pilih Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $departmentId == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ $date }}">
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
                            <label>Group</label>
                            <select name="group_id" id="group_id" class="form-control"
                                {{ !$departmentId ? 'disabled' : '' }}>
                                <option value="">
                                    {{ $departmentId ? 'Semua Group' : 'Pilih department dulu' }}
                                </option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" {{ $groupId == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a href="{{ route('admin.attendance.create') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>
        </div>

        @if (!$departmentId)
            <div class="alert alert-warning">
                Pilih department terlebih dahulu untuk menampilkan daftar karyawan.
            </div>
        @else
            {{-- FORM ABSENSI --}}
            <form method="POST" action="{{ route('admin.attendance.bulk.store') }}">
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
                                    <th>Alfa</th>
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

        // ====== AJAX: pilih Department -> load Group ======
        (function() {
            const departmentSelect = document.getElementById('department_id');
            const groupSelect = document.getElementById('group_id');
            const groupsUrl = "{{ route('admin.attendance.groups-by-department') }}";

            if (!departmentSelect || !groupSelect) {
                return;
            }

            departmentSelect.addEventListener('change', function() {
                const departmentId = this.value;

                // reset & disable dulu selama proses fetch
                groupSelect.disabled = true;
                groupSelect.innerHTML = '<option value="">Memuat group...</option>';

                if (!departmentId) {
                    groupSelect.innerHTML = '<option value="">Pilih department dulu</option>';
                    return;
                }

                fetch(`${groupsUrl}?department_id=${departmentId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(groups => {
                        let options = '<option value="">Semua Group</option>';

                        groups.forEach(group => {
                            options += `<option value="${group.id}">${group.name}</option>`;
                        });

                        groupSelect.innerHTML = options;
                        groupSelect.disabled = false;
                    })
                    .catch(() => {
                        groupSelect.innerHTML = '<option value="">Gagal memuat group</option>';
                        groupSelect.disabled = false;
                    });
            });
        })();
    </script>
@endpush
