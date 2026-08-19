    @extends('layouts.master')

    @section('content')
        <div class="section-header">
            <h1>Product Group</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ route('admin.product-group.index') }}">Product Group</a>
                </div>
                <div class="breadcrumb-item">Edit</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Edit Product Group</h4>
            </div>

            <div class="card-body p-0">
                <form action="{{ route('admin.product-group.update', $productGroup->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <div class="form-group">
                            <label>Department</label>

                            <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">

                                <option value="">-- Pilih Department --</option>

                                @foreach ($departmentList as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $productGroup->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Nama Product Group</label>

                            <input type="text" name="name" value="{{ old('name', $productGroup->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Masukkan nama product group...">

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>

                        <a href="{{ route('admin.product-group.index') }}" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>

                </form>
            </div>
        </div>
    @endsection
