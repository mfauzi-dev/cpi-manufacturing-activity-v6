@extends('layouts.master')

@section('title', 'Create User')

@section('content')

    <div class="section-header">
        <h1>Create User</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.user.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Name</label>

                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}">

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Email</label>

                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}">

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">

                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Role</label>

                    <select name="role_id" class="form-control @error('role_id') is-invalid @enderror">
                        <option value="">-- Pilih Role --</option>

                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('role_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Department</label>

                    <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                        <option value="">-- Pilih Department (opsional) --</option>

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

                <button type="submit" class="btn btn-primary">
                    Simpan
                </button>

                <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">
                    Kembali
                </a>


            </form>

        </div>
    </div>

@endsection
