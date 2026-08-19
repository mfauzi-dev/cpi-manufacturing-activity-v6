@extends('layouts.master')

@section('title', 'Create Product')

@section('content')

    <div class="section-header">
        <h1>Create Product</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.product.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama Department</label>
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

                <div class="col-md-3 mb-2">
                    <label>Nama Cost Center</label>
                    <select name="cost_center_id" id="cost_center_id" class="form-control"
                        {{ !$departmentId ? 'disabled' : '' }}>
                        <option value="">
                            {{ $departmentId ? 'Semua Cost Center' : 'Pilih department dulu' }}
                        </option>
                        @foreach ($costCenters as $costCenter)
                            <option value="{{ $costCenter->id }}" {{ $costCenterId == $costCenter->id ? 'selected' : '' }}>
                                {{ $costCenter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Nama SKU</label>

                    <input type="text" name="nama_sku" class="form-control @error('nama_sku')is-invalid @enderror">
                    @error('nama_sku')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Harga per KG</label>

                    <input type="number"
                        name="harga_per_kg"class="form-control @error('harga_per_kg')is-invalid @enderror">
                    @error('harga_per_kg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </form>

        </div>
    </div>

@endsection
