@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Edit Daily Activity Slaughter House</h1>
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

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin-production.daily-activity-slaughter-house.update', $detail->id) }}"
                method="POST">

                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" class="form-control"
                        value="{{ $detail->dailyActivitySlaughterHouse->tanggal->format('Y-m-d') }}" readonly>
                </div>

                <div class="form-group">
                    <label>Cost Center</label>
                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivitySlaughterHouse->costCenter->name ?? '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>PS Group</label>
                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivitySlaughterHouse->psGroup->name ?? '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Product Group</label>
                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivitySlaughterHouse->productGroup->name ?? '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Line</label>
                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivitySlaughterHouse->line->name ?? '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Employee</label>
                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivitySlaughterHouse->employee->nik ?? '' }} - {{ $detail->dailyActivitySlaughterHouse->employee->name ?? '-' }}"
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
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Output KG</label>

                    <input type="number" step="0.01" min="0" name="total_kg" id="total_kg"
                        value="{{ old('total_kg', $detail->total_kg) }}"
                        class="form-control @error('total_kg') is-invalid @enderror">

                    @error('total_kg')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Lama Packing</label>

                    <input type="number" step="0.01" min="0" name="lama_packing" id="lama_packing"
                        value="{{ old('lama_packing', $detail->lama_packing) }}"
                        class="form-control @error('lama_packing') is-invalid @enderror">

                    @error('lama_packing')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
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

                    <input type="text" id="total_rupiah_display" class="form-control"
                        value="Rp {{ number_format($detail->total_harga, 2, ',', '.') }}" readonly>

                    <small class="form-text text-muted">
                        Output KG × Harga per KG
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update
                </button>

                <a href="{{ route('admin-production.daily-activity-slaughter-house.detail', [
                    'costCenter' => $detail->dailyActivitySlaughterHouse->cost_center_id,
                    'psGroup' => $detail->dailyActivitySlaughterHouse->ps_group_id,
                    'date_from' => $detail->dailyActivitySlaughterHouse->tanggal->format('Y-m-d'),
                    'date_to' => $detail->dailyActivitySlaughterHouse->tanggal->format('Y-m-d'),
                    'lineId' => $detail->dailyActivitySlaughterHouse->line_id,
                ]) }}"
                    class="btn btn-secondary">
                    Kembali
                </a>

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

            $('#harga_per_kg_display').val(
                'Rp ' + Number(harga).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
            );

            hitungTotal();

        });

        $(document).on('keyup change', '#total_kg', function() {
            hitungTotal();
        });

        function hitungTotal() {

            let kg = parseFloat($('#total_kg').val()) || 0;

            let harga = $('#product').find(':selected').data('price') || 0;

            let total = kg * harga;

            $('#total_rupiah_display').val(
                'Rp ' + Number(total).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
            );

        }
    </script>
@endpush
