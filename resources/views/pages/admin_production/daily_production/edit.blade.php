@extends('layouts.master')

@section('title', 'Edit Daily Production')

@section('content')

    <div class="section-header">
        <h1>Edit Daily Production</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin-production.daily-production.update', $detail->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Tanggal</label>

                    <input type="date" name="tanggal"
                        value="{{ old('tanggal', $detail->dailyProduction->tanggal->format('Y-m-d')) }}"
                        class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label>Cost Center</label>

                    <input type="text" class="form-control" value="{{ $detail->dailyProduction->costCenter->name }}"
                        readonly>
                </div>

                <div class="form-group">
                    <label>PS Group</label>

                    <input type="text" class="form-control" value="{{ $detail->dailyProduction->psGroup->name }}"
                        readonly>
                </div>

                <div class="form-group">
                    <label>Nama Material</label>

                    <select name="product_id" id="product"
                        class="form-control select2 @error('product_id') is-invalid @enderror">
                        <option value="">-- Pilih Material --</option>
                        @foreach ($productList as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->harga_per_kg }}"
                                {{ old('product_id', $detail->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->material_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Total KG</label>

                    <input type="number" step="0.01" min="0" name="total_kg" id="total_kg"
                        value="{{ old('total_kg', $detail->total_kg) }}"
                        class="form-control @error('total_kg') is-invalid @enderror">

                    @error('total_kg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Harga per KG</label>

                    <input type="text" id="harga_per_kg_display" class="form-control"
                        value="Rp {{ number_format($detail->harga_per_kg, 2, ',', '.') }}" readonly>

                    <small class="form-text text-muted">
                        Harga otomatis mengikuti master data material yang dipilih.
                    </small>
                </div>

                <div class="form-group">
                    <label>Total Rupiah</label>

                    <input type="text" id="total_rupiah_display" class="form-control" readonly>

                    <small class="form-text text-muted">
                        Total KG x Harga per KG
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update
                </button>
            </form>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {

            $('.select2').select2({
                width: '100%'
            });

            hitungTotal();

        });

        $(document).on('change', '#product', function() {

            let harga = $(this).find(':selected').data('price') || 0;

            $('#harga_per_kg_display').val('Rp ' + Number(harga).toLocaleString('id-ID'));

            hitungTotal();

        });

        $(document).on('keyup change', '#total_kg', function() {

            hitungTotal();

        });

        function hitungTotal() {

            let kg = parseFloat($('#total_kg').val()) || 0;

            let harga = $('#product').find(':selected').data('price') || 0;

            let total = kg * harga;

            $('#total_rupiah_display').val('Rp ' + Number(total).toLocaleString('id-ID'));

        }
    </script>
@endpush
