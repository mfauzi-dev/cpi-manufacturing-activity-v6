@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>PS Group</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.ps-group.index') }}">PS Group</a>
            </div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Edit PS Group</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.ps-group.update', $psGroup->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label>Cost Center</label>

                        <select name="cost_center_id" class="form-control @error('cost_center_id') is-invalid @enderror">

                            <option value="">-- Pilih Cost Center --</option>

                            @foreach ($costCenterList as $costCenter)
                                <option value="{{ $costCenter->id }}"
                                    {{ old('cost_center_id', $psGroup->cost_center_id) == $costCenter->id ? 'selected' : '' }}>
                                    {{ $costCenter->code }} - {{ $costCenter->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('cost_center_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama PS Group</label>

                        <input type="text" name="name" value="{{ old('name', $psGroup->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Masukkan nama PS Group...">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>

                    <a href="{{ route('admin.ps-group.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
@endsection
