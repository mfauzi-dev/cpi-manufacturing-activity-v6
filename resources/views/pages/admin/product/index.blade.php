@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Product</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Product</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    <div class="section-body">
        <a href="{{ route('admin.product.create') }}" class="btn btn-primary mb-4">
            <i class="fas fa-plus"></i> Tambah Product
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
            <form action="{{ route('admin.product.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-lg mb-3">
                        <select name="department_id" class="form-control">
                            <option value="">Semua Department</option>
                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama Product."
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
                            <th>Nama Department</th>
                            <th>Nama SKU</th>
                            <th>Harga per KG</th>
                            <th width="180" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td>{{ $product->department->name }}</td>
                                <td>{{ $product->nama_sku }}</td>
                                <td>Rp {{ number_format($product->harga_per_kg) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.product.edit', $product->id) }}"
                                        class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    {{-- <form action="{{ route('admin.product.destroy', $product->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data?')">
                                            Hapus
                                        </button>
                                    </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    Daftar product belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-right">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
@endsection
