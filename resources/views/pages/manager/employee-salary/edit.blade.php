@extends('layouts.master')

@section('content')

    <div class="section-header">

        <h1>Employee Salary</h1>

        <div class="section-header-breadcrumb">

            <div class="breadcrumb-item active">
                <a href="{{ route('manager.employee-salary.index') }}">
                    Employee Salary
                </a>
            </div>

            <div class="breadcrumb-item">
                Edit
            </div>

        </div>

    </div>

    <div class="section-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Edit Employee Salary</h4>
            </div>

            <form action="{{ route('manager.employee-salary.update', $employeeSalary->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">

                        <label>Karyawan</label>

                        <select name="employee_id" id="employee_id"
                            class="form-control select2 @error('employee_id') is-invalid @enderror">

                            <option value="">Pilih Karyawan</option>

                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ old('employee_id', $employeeSalary->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->nik ?? '-' }} - {{ $employee->name }}

                                    @if ($employee->department)
                                        ({{ $employee->department->name }})
                                    @endif
                                </option>
                            @endforeach

                        </select>

                        @error('employee_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">

                        <label>Tahun</label>

                        <select name="tahun" class="form-control @error('tahun') is-invalid @enderror">

                            <option value="">Pilih Tahun</option>

                            @for ($year = date('Y') + 1; $year >= date('Y') - 5; $year--)
                                <option value="{{ $year }}"
                                    {{ old('tahun', $employeeSalary->tahun) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor

                        </select>

                        @error('tahun')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">

                        <label>Basic Salary</label>

                        <input type="number" name="basic_salary"
                            class="form-control @error('basic_salary') is-invalid @enderror"
                            value="{{ old('basic_salary', $employeeSalary->basic_salary) }}"
                            placeholder="Masukkan basic salary" min="0" step="0.01">

                        @error('basic_salary')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="card-footer text-right">

                    <a href="{{ route('manager.employee-salary.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            $('#employee_id').select2({
                placeholder: 'Pilih Karyawan',
                allowClear: true,
                width: '100%'
            });

        });
    </script>
@endpush
