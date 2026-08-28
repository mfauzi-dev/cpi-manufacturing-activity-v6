@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Konfigurasi UMP</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.wage-config.index') }}">Konfigurasi UMP</a></div>
            <div class="breadcrumb-item">Tambah</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Tambah UMP</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.wage-config.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" value="{{ old('tahun', now()->year) }}"
                            class="form-control @error('tahun') is-invalid @enderror" placeholder="Masukkan tahun..">
                        @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>UMP (Rp)</label>
                        <input type="number" step="0.01" name="ump" value="{{ old('ump') }}"
                            class="form-control @error('ump') is-invalid @enderror" placeholder="Masukkan nominal UMP..">
                        @error('ump')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Hari Kerja Standar</label>
                        <input type="number" name="hari_kerja_standar" value="{{ old('hari_kerja_standar', 25) }}"
                            class="form-control @error('hari_kerja_standar') is-invalid @enderror"
                            placeholder="Masukkan jumlah hari kerja standar..">
                        @error('hari_kerja_standar')
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
