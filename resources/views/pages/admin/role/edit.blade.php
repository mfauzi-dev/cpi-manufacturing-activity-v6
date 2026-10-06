@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Role</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('role.index') }}">Role</a></div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Edit Role</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('role.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Role</label>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama role..">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
