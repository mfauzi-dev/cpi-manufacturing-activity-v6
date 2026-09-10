@extends('layouts.master')

@section('content')
    <div class="section-header">

        <h1>Shift</h1>

        <div class="section-header-breadcrumb">

            <div class="breadcrumb-item active">
                <a href="{{ route('admin.shift.index') }}">
                    Shift
                </a>
            </div>

            <div class="breadcrumb-item">
                Tambah
            </div>

        </div>

    </div>

    <div class="card">

        <div class="card-header">
            <h4>Tambah Shift</h4>
        </div>

        <div class="card-body p-0">

            <form action="{{ route('admin.shift.store') }}" method="POST">

                @csrf

                <div class="card-body">

                    <div class="form-group">

                        <label>Department</label>

                        <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                            <option value="">-- Pilih Department --</option>

                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('department_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">

                        <label>Nama Shift</label>

                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama shift..">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="card-footer text-right">

                    <a href="{{ route('admin.shift.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>
@endsection
