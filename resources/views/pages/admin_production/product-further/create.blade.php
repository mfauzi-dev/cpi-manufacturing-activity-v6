@extends('layouts.master')

@section('title', 'Create Product Further')

@section('content')

    <div class="section-header">
        <h1>Create Product Further</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin-production.product-further.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama Cost Center</label>
                    <select name="cost_center_id" class="form-control @error('cost_center_id') is-invalid @enderror">
                        <option value="">-- Pilih Cost Center --</option>
                        @foreach ($costCenterList as $costCenter)
                            <option value="{{ $costCenter->id }}"
                                {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                {{ $costCenter->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('cost_center_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Process Type</label>
                    <select name="process_type_id" class="form-control @error('process_type_id') is-invalid @enderror">
                        <option value="">-- Pilih Process Type --</option>
                        @foreach ($processTypeList as $processType)
                            <option value="{{ $processType->id }}"
                                {{ old('process_type_id') == $processType->id ? 'selected' : '' }}>
                                {{ $processType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('process_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Nama Product Group</label>
                    <select name="product_group_id" class="form-control @error('product_group_id') is-invalid @enderror">
                        <option value="">-- Pilih Product Group --</option>
                        @foreach ($productGroupList as $productGroup)
                            <option value="{{ $productGroup->id }}"
                                {{ old('product_group_id') == $productGroup->id ? 'selected' : '' }}>
                                {{ $productGroup->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_group_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Kode Material</label>

                    <input type="text" name="kode_material"
                        class="form-control @error('kode_material') is-invalid @enderror">
                    @error('kode_material')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Nama Material</label>

                    <input type="text" name="nama_material"
                        class="form-control @error('nama_material') is-invalid @enderror">
                    @error('nama_material')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>

                <a href="{{ route('admin-production.product-further.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </form>

        </div>
    </div>

@endsection
