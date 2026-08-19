@extends('layouts.master')

@section('title', 'Edit Product')

@section('content')

    <div class="section-header">
        <h1>Edit Product</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.product.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Department --}}
                <div class="form-group">
                    <label>Nama Department</label>
                    <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                        <option value="">-- Pilih Department --</option>

                        @foreach ($departmentList as $department)
                            <option value="{{ $department->id }}"
                                {{ old('department_id', $product->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nama SKU --}}
                <div class="form-group">
                    <label>Nama SKU</label>

                    <input type="text" name="nama_sku" value="{{ old('nama_sku', $product->nama_sku) }}"
                        class="form-control @error('nama_sku') is-invalid @enderror">

                    @error('nama_sku')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Harga per KG --}}
                <div class="form-group">
                    <label>Harga per KG</label>

                    <input type="number" name="harga_per_kg"
                        value="{{ old('harga_per_kg', (int) $product->harga_per_kg) }}"
                        class="form-control @error('harga_per_kg') is-invalid @enderror">

                    @error('harga_per_kg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Update
                </button>

                <a href="{{ route('admin.product.index') }}" class="btn btn-secondary">
                    Batal
                </a>

            </form>

        </div>
    </div>

@endsection
