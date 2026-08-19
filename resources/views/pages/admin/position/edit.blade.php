@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Posisi</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('position.index') }}">Posisi</a></div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Edit Posisi</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('position.update', $position->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label>Nama Posisi</label>
                        <input type="text" name="name" value="{{ old('name', $position->name) }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama position..">
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
