@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Cost Center</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.cost-center.index') }}">Cost Center</a>
            </div>
            <div class="breadcrumb-item">Tambah</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Tambah Cost Center</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.cost-center.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <div class="form-group">
                        <label>Department</label>

                        <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                            <option value="">-- Pilih Department --</option>

                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Code</label>

                        <input type="text" name="code" value="{{ old('code') }}"
                            class="form-control @error('code') is-invalid @enderror"
                            placeholder="Masukkan code cost center...">

                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama Cost Center</label>

                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Masukkan nama cost center...">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>

                    <a href="{{ route('admin.cost-center.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
@endsection
