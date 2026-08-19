{{-- resources/views/daily-production/import.blade.php --}}
@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Import Daily Production</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('admin-production.daily-activity.upload') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Tanggal</label>

                        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', date('Y-m-d')) }}">

                        @error('tanggal')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Cost Center</label>

                        <select name="cost_center_id" class="form-control @error('cost_center_id') is-invalid @enderror">

                            <option value="">Pilih Cost Center</option>

                            @foreach ($costCenters as $costCenter)
                                <option value="{{ $costCenter->id }}"
                                    {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                    {{ $costCenter->code }} - {{ $costCenter->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('cost_center_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>File Excel</label>

                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                            accept=".xlsx,.xls">

                        <small class="form-text text-muted">
                            Header wajib berada di baris ke-4 (No, Material Code, Products, Total KG).
                        </small>

                        @error('file')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            Upload & Import
                        </button>

                        <a href="{{ route('admin-production.daily-activity.index') }}" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection
