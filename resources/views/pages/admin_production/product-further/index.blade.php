@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Product Further</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Product Further</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    <div class="section-body">
        <a href="{{ route('admin-production.product-further.create') }}" class="btn btn-primary mb-4">
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
            <form action="{{ route('admin-production.product-further.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-lg mb-3">
                        <select name="cost_center_id" class="form-control">
                            <option value="">Semua Cost Center</option>
                            @foreach ($costCenterList as $costCenter)
                                <option value="{{ $costCenter->id }}"
                                    {{ $costCenterId == $costCenter->id ? 'selected' : '' }}>
                                    {{ $costCenter->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg mb-3">
                        <select name="process_type_id" class="form-control">
                            <option value="">Semua Process Type</option>
                            @foreach ($processTypeList as $processType)
                                <option value="{{ $processType->id }}"
                                    {{ $processTypeId == $processType->id ? 'selected' : '' }}>
                                    {{ $processType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg mb-3">
                        <select name="product_group_id" class="form-control">
                            <option value="">Semua Product Group</option>
                            @foreach ($productGroupList as $productGroup)
                                <option value="{{ $productGroup->id }}"
                                    {{ $productGroupId == $productGroup->id ? 'selected' : '' }}>
                                    {{ $productGroup->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama Product."
                            value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="mt-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin-production.product-further.index') }}" class="btn btn-secondary">Reset</a>
                </div>

            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Daftar Product Further</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th witdth="60">No.</th>
                            <th>Nama Cost Center</th>
                            <th>Process Type</th>
                            <th>Nama Product Group</th>
                            <th>Kode Material</th>
                            <th>Nama Material</th>
                            <th width="180" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td>{{ $product->costCenter->name }}</td>
                                <td>{{ $product->processType->name ?? '-' }}</td>
                                <td>{{ $product->productGroup->name ?? '-' }}</td>
                                <td>{{ $product->material_code ?? '-' }}</td>
                                <td>{{ $product->material_name }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin-production.product-further.edit', $product->id) }}"
                                        class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin-production.product-further.destroy', $product->id) }}"
                                        method="POST" style="display:inline;">
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
                                <td colspan="7" class="text-center">
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
