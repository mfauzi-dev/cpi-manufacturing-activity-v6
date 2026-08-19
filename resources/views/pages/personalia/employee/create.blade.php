@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Tambah Karyawan</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Tambah Employee</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('personalia.employee.store') }}" method="POST">
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

                {{-- KTP NUMBER --}}
                <div class="form-group">
                    <label>No KTP</label>
                    <input type="text" name="ktp_number" value="{{ old('ktp_number') }}"
                        class="form-control @error('ktp_number') is-invalid @enderror" placeholder="Masukkan No KTP">
                    @error('ktp_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- EMPLOYMENT STATUS (Permanent / Outsourcing) --}}
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

                {{-- OUTSOURCING (muncul kalau employment_status = outsourcing) --}}
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

                {{-- EMPLOYEE STATUS: Borongan/Harian (muncul kalau outsourcing). Kalau Permanent, otomatis "cpi" di backend --}}
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

                {{-- COST CENTER --}}
                <div class="form-group">
                    <label>Cost Center</label>
                    <select name="cost_center_id" class="form-control @error('cost_center_id') is-invalid @enderror">
                        <option value="">-- Pilih Cost Center --</option>
                        @foreach ($costCenters as $cc)
                            <option value="{{ $cc->id }}" {{ old('cost_center_id') == $cc->id ? 'selected' : '' }}>
                                {{ $cc->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('cost_center_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PS GROUP --}}
                <div class="form-group">
                    <label>Group</label>
                    <select name="ps_group_id" class="form-control @error('ps_group_id') is-invalid @enderror">
                        <option value="">-- Pilih Group --</option>
                        @foreach ($psGroups as $group)
                            <option value="{{ $group->id }}" {{ old('ps_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ps_group_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- POSITION --}}
                <div class="form-group">
                    <label>Posisi</label>
                    <select name="position_id" class="form-control @error('position_id') is-invalid @enderror">
                        <option value="">-- Pilih Posisi --</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}"
                                {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('position_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- GENDER --}}
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                        <option value="">-- Pilih Gender --</option>
                        <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- BIRTHPLACE --}}
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="birthplace" value="{{ old('birthplace') }}"
                        class="form-control @error('birthplace') is-invalid @enderror">
                    @error('birthplace')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- RELIGIOUS --}}
                <div class="form-group">
                    <label>Agama</label>
                    <input type="text" name="religious" value="{{ old('religious') }}"
                        class="form-control @error('religious') is-invalid @enderror">
                    @error('religious')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- JOIN DATE --}}
                <div class="form-group">
                    <label>Tanggal Masuk</label>
                    <input type="date" name="join_date" value="{{ old('join_date') }}"
                        class="form-control @error('join_date') is-invalid @enderror">
                    @error('join_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PERSONNEL AREA --}}
                <div class="form-group">
                    <label>Personnel Area</label>
                    <input type="text" name="personnel_area" value="{{ old('personnel_area') }}"
                        class="form-control @error('personnel_area') is-invalid @enderror">
                    @error('personnel_area')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PERS NO --}}
                <div class="form-group">
                    <label>Pers No</label>
                    <input type="text" name="pers_no" value="{{ old('pers_no') }}"
                        class="form-control @error('pers_no') is-invalid @enderror">
                    @error('pers_no')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PERSONAL NUMBER --}}
                <div class="form-group">
                    <label>Personal Number</label>
                    <input type="text" name="personal_number" value="{{ old('personal_number') }}"
                        class="form-control @error('personal_number') is-invalid @enderror">
                    @error('personal_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- BUTTON --}}
                <div class="text-right">
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
        function toggleOutsourcingFields() {
            const status = document.getElementById('employment_status').value;
            const outsourcingWrapper = document.getElementById('outsourcing-wrapper');
            const employeeStatusWrapper = document.getElementById('employee-status-wrapper');

            if (status === 'outsourcing') {
                outsourcingWrapper.classList.remove('d-none');
                employeeStatusWrapper.classList.remove('d-none');
            } else {
                outsourcingWrapper.classList.add('d-none');
                employeeStatusWrapper.classList.add('d-none');
            }
        }

        document.getElementById('employment_status').addEventListener('change', toggleOutsourcingFields);

        // jalankan sekali pas halaman dimuat, buat handle kasus validation error
        // (form ke-render ulang dengan old('employment_status') yang udah keisi)
        toggleOutsourcingFields();
    </script>
@endpush
