@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Line</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.line.index') }}">Line</a>
            </div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Edit Line</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.line.update', $line->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label>Department</label>

                        <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">

                            <option value="">-- Pilih Department --</option>

                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $line->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama Line</label>

                        <input type="text" name="name" value="{{ old('name', $line->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama Line...">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>

                    <a href="{{ route('admin.line.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
@endsection
