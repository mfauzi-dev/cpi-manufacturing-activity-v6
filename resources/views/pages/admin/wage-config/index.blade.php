@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Konfigurasi UMP</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Konfigurasi UMP</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    <div class="section-body">
        <a href="{{ route('admin.wage-config.create') }}" class="btn btn-primary mb-4">
            <i class="fas fa-plus"></i> Tambah UMP
        </a>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('danger') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="#" method="GET">
                <div class="row g-2">
                    <div class="col-lg col-md-4 mb-3">
                        <input type="text" name="search" class="form-control mb-4" placeholder="Cari tahun.."
                            value="{{ $search ?? '' }}">
                    </div>

                    <div class="col-lg-2 col-md-12">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Daftar UMP per Tahun</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th witdth="60">No.</th>
                            <th>Tahun</th>
                            <th class="text-center">UMP</th>
                            <th class="text-center">Hari Kerja Standar</th>
                            <th width="180" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($wageConfigs as $index => $config)
                            <tr>
                                <td>{{ $wageConfigs->firstItem() + $index }}</td>
                                <td>{{ $config->tahun }}</td>
                                <td class="text-center">Rp {{ number_format($config->ump, 2) }}</td>
                                <td class="text-center">{{ $config->hari_kerja_standar }} hari</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.wage-config.edit', $config->id) }}"
                                        class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.wage-config.destroy', $config->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    Data UMP belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-right">
            {{ $wageConfigs->withQueryString()->links() }}
        </div>
    </div>
@endsection