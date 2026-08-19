@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Karyawan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('employee.index') }}">Karyawan</a></div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Edit Karyawan</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('employee.update', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label>Nama Karyawan</label>
                        <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama karyawan..">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>NIK</label>
                        <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}"
                            class="form-control @error('nik') is-invalid @enderror" placeholder="Masukkan nik..">
                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama Department</label>
                        <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                            <option value="">-- Pilih Department --</option>
                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $employee->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama Position</label>
                        <select name="position_id" class="form-control @error('position_id') is-invalid @enderror">
                            <option value="">-- Pilih Position --</option>
                            @foreach ($positionList as $position)
                                <option value="{{ $position->id }}"
                                    {{ old('position_id', $employee->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                    {{ $position->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('position_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama Foundation</label>
                        <select name="foundation_id" class="form-control @error('foundation_id') is-invalid @enderror">

                            @foreach ($foundationList as $foundation)
                                <option value="{{ $foundation->id }}"
                                    {{ old('foundation_id', $employee->foundation_id ?? '') == $foundation->id ? 'selected' : '' }}>
                                    {{ $foundation->name }}
                                </option>
                            @endforeach
                            @error('foundation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tipe Karyawan</label>
                        <select name="employee_type" class="form-control @error('employee_type') is-invalid @enderror">
                            <option value="">-- Pilih Tipe Karyawan --</option>

                            <option value="BORONGAN"
                                {{ old('employee_type', $employee->employee_type ?? '') == 'BORONGAN' ? 'selected' : '' }}>
                                BORONGAN
                            </option>

                            <option value="OUTSOURCING"
                                {{ old('employee_type', $employee->employee_type ?? '') == 'OUTSOURCING' ? 'selected' : '' }}>
                                OUTSOURCING
                            </option>
                        </select>
                        @error('employee_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="">-- Pilih Status --</option>

                            <option value="ACTIVE"
                                {{ old('status', $employee->status ?? '') == 'ACTIVE' ? 'selected' : '' }}>
                                ACTIVE
                            </option>

                            <option value="INACTIVE"
                                {{ old('status', $employee->status ?? '') == 'INACTIVE' ? 'selected' : '' }}>
                                INACTIVE
                            </option>

                            <option value="RESIGN"
                                {{ old('status', $employee->status ?? '') == 'RESIGN' ? 'selected' : '' }}>
                                RESIGN
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Base Salary</label>
                        <input type="text" name="base_salary"
                            value="{{ old('base_salary', (int) $employee->base_salary) }}"
                            class="form-control @error('base_salary') is-invalid @enderror"
                            placeholder="Masukkan Base Salary..">
                        @error('base_salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
