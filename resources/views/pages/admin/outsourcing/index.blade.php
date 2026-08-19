@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Yayasan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Yayasan</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    <div class="section-body">
        <a href="{{ route('admin.outsourcing.create') }}" class="btn btn-primary mb-4">
            <i class="fas fa-plus"></i> Tambah Yayasan
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
                        <input type="text" name="search" class="form-control mb-4" placeholder="Cari nama yayasan.."
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
            <h4>Daftar Yayasan</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th witdth="60">No.</th>
                            <th>Name</th>
                            <th width="180" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($outsourcings as $index => $outsourcing)
                            <tr>
                                <td>{{ $outsourcings->firstItem() + $index }}</td>
                                <td>{{ $outsourcing->name }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.outsourcing.edit', $outsourcing->id) }}"
                                        class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.outsourcing.destroy', $outsourcing->id) }}" method="POST"
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
                                <td colspan="3" class="text-center">
                                    Daftar yayasan belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-right">
            {{ $outsourcings->withQueryString()->links() }}
        </div>
    </div>
@endsection
