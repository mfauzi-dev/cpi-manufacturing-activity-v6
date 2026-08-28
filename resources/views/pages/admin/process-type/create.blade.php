@extends('layouts.master')

@section('title', 'Create Process Type')

@section('content')

    <div class="section-header">
        <h1>Create Process Type</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.process-type.store') }}" method="POST">
                @csrf

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
                    <label>Nama Process Type</label>

                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror">

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>

                <a href="{{ route('admin.process-type.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </form>

        </div>
    </div>

@endsection
