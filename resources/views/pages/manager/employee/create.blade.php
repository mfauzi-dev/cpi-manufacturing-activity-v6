@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Tambah Karyawan</h1>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Tambah Employee</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('manager.employee.store') }}" method="POST">
                @csrf

                {{-- NIK --}}
                <div class="form-group">
                    <label>NIK</label>

                    <input type="text" name="nik" value="{{ old('nik') }}"
                        class="form-control @error('nik') is-invalid @enderror" placeholder="Masukkan NIK">

                    @error('nik')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                {{-- NAME --}}
                <div class="form-group">
                    <label>Nama</label>

                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan Nama">

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                {{-- STATUS AKTIF --}}
                <div class="form-group">
                    <label>Status Aktif</label>

                    <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">

                        <option value="">-- Pilih Status Aktif --</option>

                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>

                    </select>

                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                {{-- EMPLOYMENT STATUS --}}
                <div class="form-group">
                    <label>Jenis Karyawan</label>

                    <select name="employment_status" id="employment_status"
                        class="form-control @error('employment_status') is-invalid @enderror">

                        <option value="">-- Pilih Jenis Karyawan --</option>

                        <option value="permanent" {{ old('employment_status') == 'permanent' ? 'selected' : '' }}>
                            Permanent
                        </option>

                        <option value="outsourcing" {{ old('employment_status') == 'outsourcing' ? 'selected' : '' }}>
                            Outsourcing
                        </option>

                    </select>

                    @error('employment_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                {{-- OUTSOURCING --}}
                <div class="form-group d-none" id="outsourcing-wrapper">

                    <label>Outsourcing</label>

                    <select name="outsourcing_id" class="form-control @error('outsourcing_id') is-invalid @enderror">

                        <option value="">-- Pilih Outsourcing --</option>

                        @foreach ($outsourcingList as $os)
                            <option value="{{ $os->id }}" {{ old('outsourcing_id') == $os->id ? 'selected' : '' }}>
                                {{ $os->name }}
                            </option>
                        @endforeach

                    </select>

                    @error('outsourcing_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>


                {{-- EMPLOYEE STATUS --}}
                <div class="form-group d-none" id="employee-status-wrapper">

                    <label>Status Karyawan</label>

                    <select name="employee_status" class="form-control @error('employee_status') is-invalid @enderror">

                        <option value="">-- Pilih Status Karyawan --</option>

                        <option value="cpi" {{ old('employee_status') == 'cpi' ? 'selected' : '' }}>
                            CPI
                        </option>

                        <option value="borongan" {{ old('employee_status') == 'borongan' ? 'selected' : '' }}>
                            Borongan
                        </option>

                        <option value="harian" {{ old('employee_status') == 'harian' ? 'selected' : '' }}>
                            Harian
                        </option>

                    </select>

                    @error('employee_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>


                {{-- DEPARTMENT --}}
                <div class="form-group">

                    <label>Department</label>

                    <input type="text"class="form-control" value="{{ auth()->user()->department->name ?? '-' }}"
                        readonly>

                    <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">

                    @error('department_id')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- COST CENTER --}}
                <div class="form-group">

                    <label>Cost Center</label>

                    <select name="cost_center_id" id="cost_center_id"
                        class="form-control @error('cost_center_id') is-invalid @enderror">

                        <option value="">-- Pilih Cost Center --</option>

                        @foreach ($costCenterList as $costCenter)
                            <option value="{{ $costCenter->id }}"
                                {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>

                                {{ $costCenter->code }} - {{ $costCenter->name }}

                            </option>
                        @endforeach

                    </select>

                    @error('cost_center_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- PS GROUP --}}
                <div class="form-group">

                    <label>Group</label>

                    <select name="ps_group_id" id="ps_group_id"
                        class="form-control @error('ps_group_id') is-invalid @enderror">

                        <option value="">-- Pilih Group --</option>

                    </select>

                    @error('ps_group_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- POSITION --}}
                <div class="form-group">

                    <label>Posisi</label>

                    <select name="position_id" class="form-control @error('position_id') is-invalid @enderror">

                        <option value="">-- Pilih Posisi --</option>

                        @foreach ($positionList as $position)
                            <option value="{{ $position->id }}"
                                {{ old('position_id') == $position->id ? 'selected' : '' }}>

                                {{ $position->name }}

                            </option>
                        @endforeach

                    </select>

                    @error('position_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- GENDER --}}
                <div class="form-group">

                    <label>Gender</label>

                    <select name="gender" class="form-control @error('gender') is-invalid @enderror">

                        <option value="">-- Pilih Gender --</option>

                        <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>
                            Laki-laki
                        </option>

                        <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>
                            Perempuan
                        </option>

                    </select>

                    @error('gender')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- PERSONNEL AREA --}}
                <div class="form-group">

                    <label>Personel Area</label>

                    <input type="text" name="personel_area" value="{{ old('personel_area') }}"
                        class="form-control @error('personel_area') is-invalid @enderror">

                    @error('personel_area')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- BUTTON --}}
                <div class="text-right">

                    <a href="{{ route('manager.employee.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>

                </div>

            </form>

        </div>
    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==========================================
            // EMPLOYMENT STATUS
            // ==========================================
            const employmentStatus = document.getElementById('employment_status');
            const outsourcingWrapper = document.getElementById('outsourcing-wrapper');
            const employeeStatusWrapper = document.getElementById('employee-status-wrapper');

            function toggleOutsourcingFields() {
                if (employmentStatus.value === 'outsourcing') {
                    outsourcingWrapper.classList.remove('d-none');
                    employeeStatusWrapper.classList.remove('d-none');
                } else {
                    outsourcingWrapper.classList.add('d-none');
                    employeeStatusWrapper.classList.add('d-none');
                }
            }

            employmentStatus.addEventListener('change', toggleOutsourcingFields);
            toggleOutsourcingFields();


            // ==========================================
            // COST CENTER & PS GROUP
            // ==========================================
            const costCenter = document.getElementById('cost_center_id');
            const psGroup = document.getElementById('ps_group_id');

            const selectedPsGroup = "{{ old('ps_group_id') }}";


            // ==========================================
            // LOAD PS GROUP
            // ==========================================
            function loadPsGroups(costCenterId, selected = null) {

                psGroup.innerHTML = '<option value="">-- Pilih Group --</option>';

                if (!costCenterId) {
                    return;
                }

                fetch(`/employee/ps-groups/${costCenterId}`)
                    .then(response => {

                        if (!response.ok) {
                            throw new Error(
                                'HTTP error: ' + response.status
                            );
                        }

                        return response.json();
                    })
                    .then(data => {

                        console.log('PS Group:', data);

                        data.forEach(item => {

                            const option = document.createElement('option');

                            option.value = item.id;
                            option.textContent = item.name;

                            if (selected && selected == item.id) {
                                option.selected = true;
                            }

                            psGroup.appendChild(option);
                        });
                    })
                    .catch(error => {

                        console.error(
                            'Gagal mengambil PS Group:',
                            error
                        );
                    });
            }


            // ==========================================
            // COST CENTER CHANGE
            // ==========================================
            costCenter.addEventListener('change', function() {

                loadPsGroups(this.value);

            });


            // ==========================================
            // LOAD OLD PS GROUP
            // ==========================================
            if (costCenter.value) {

                loadPsGroups(
                    costCenter.value,
                    selectedPsGroup
                );

            }

        });
    </script>
@endpush
