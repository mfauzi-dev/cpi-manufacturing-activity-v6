@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Input Daily Production</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <form action="{{ route('admin-production.daily-production.store') }}" method="POST" id="dailyProductionForm">

            @csrf

            <div class="card">

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal</label>

                                <input type="date" name="tanggal" class="form-control"
                                    value="{{ old('tanggal', date('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>

                                <input type="text" class="form-control" value="{{ $department->name }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Cost Center</label>

                                <select name="cost_center_id" id="cost_center"
                                    class="form-control @error('cost_center_id') is-invalid @enderror">

                                    <option value="">Pilih Cost Center</option>

                                    @foreach ($costCenterList as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->code }} - {{ $costCenter->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('cost_center_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Group</label>

                                <select id="ps_group" name="ps_group_id"
                                    class="form-control @error('ps_group_id') is-invalid @enderror">

                                    <option value="">Pilih Group</option>

                                </select>

                                @error('ps_group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- DETAIL --}}
            <div class="card">

                <div class="card-header">

                    <h4>Detail Daily Production</h4>

                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered" id="detailTable" style="table-layout: fixed;">
                        <thead class="text-center">
                            <tr>
                                <th width="100">No</th>
                                <th width="400">Nama Material</th>
                                <th width="180">Total KG</th>
                                <th width="170">Harga / KG</th>
                                <th width="170">Rupiah</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr data-row-index="0">

                                <td class="text-center nomor">1</td>

                                <td>
                                    <select name="details[0][product_id]" class="form-control select2 product">
                                        <option value="">Pilih Nama Material</option>
                                    </select>
                                </td>

                                <td>
                                    <input type="number" step="0.01" name="details[0][total_kg]"
                                        class="form-control total-kg" min="0">
                                </td>

                                <td class="text-right align-middle">
                                    <span class="harga-per-kg">-</span>
                                </td>

                                <td class="text-right align-middle">
                                    <strong class="rupiah">-</strong>
                                </td>

                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm removeRow">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <div class="card-footer">

                    <div class="d-flex justify-content-between align-items-center">

                        <button type="button" id="btnAddRow" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Tambah Baris
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>

                    </div>

                </div>

            </div>

        </form>

    </div>
@endsection

@push('styles')
    <style>
        #detailTable td {
            max-width: 0;
        }

        #detailTable .select2-container {
            width: 100% !important;
        }

        #detailTable .select2-container .select2-selection__rendered {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {

            $('.select2').select2({
                width: '100%'
            });

            if ($('#cost_center').val()) {
                loadCostCenterDependents($('#cost_center').val());
            }

        });

        $('#cost_center').change(function() {

            let costCenterId = $(this).val();

            $('#ps_group').html('<option value="">Pilih Group</option>');

            if (!costCenterId) {
                $('.product').html('<option value="">Pilih SKU</option>');
                return;
            }

            loadCostCenterDependents(costCenterId);
        });

        function loadCostCenterDependents(costCenterId) {

            // PRODUCT
            $.get(
                "{{ route('admin-production.daily-production.products', ':id') }}"
                .replace(':id', costCenterId),

                function(res) {

                    let options = '<option value="">Pilih SKU</option>';

                    $.each(res, function(i, item) {
                        options += `
                        <option value="${item.id}" data-price="${item.harga_per_kg}">
                            ${item.material_name} - ${item.material_code}
                        </option>`;
                    });

                    $('.product').html(options);

                    $('#detailTable tbody tr').each(function() {
                        $(this).find('.harga-per-kg').text('-');
                        $(this).find('.rupiah').text('-');
                    });
                }
            );

            // PS GROUP
            $.get(
                "{{ route('daily-production.ps-groups', ':id') }}".replace(':id', costCenterId),
                function(data) {

                    let html = '<option value="">Pilih Group</option>';
                    let oldPsGroup = "{{ old('ps_group_id') }}";

                    $.each(data, function(i, item) {
                        let selected = (oldPsGroup && oldPsGroup == item.id) ? 'selected' : '';
                        html += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                    });

                    $('#ps_group').html(html);
                }
            );
        }

        $(document).on('click', '.removeRow', function() {

            if ($('#detailTable tbody tr').length <= 1) {
                alert('Minimal harus ada 1 baris.');
                return;
            }

            $(this).closest('tr').remove();
            renumberRows();
        });

        $(document).on('change', '.product', function() {

            let row = $(this).closest('tr');
            let harga = $(this).find(':selected').data('price') || 0;

            row.find('.harga-per-kg').text('Rp ' + Number(harga).toLocaleString('id-ID'));

            hitungRupiah(row);
        });

        $(document).on('keyup change', '.total-kg', function() {
            hitungRupiah($(this).closest('tr'));
        });

        function hitungRupiah(row) {

            let kg = parseFloat(row.find('.total-kg').val()) || 0;
            let harga = row.find('.product option:selected').data('price') || 0;

            let total = kg * harga;

            row.find('.rupiah').text('Rp ' + Number(total).toLocaleString('id-ID'));
        }

        let rowIndex = 1;

        $('#btnAddRow').click(function() {

            let productOptions = '';

            $('.product:first option').each(function() {
                productOptions += `
                <option value="${$(this).val()}" data-price="${$(this).data('price') ?? ''}">
                    ${$(this).text()}
                </option>`;
            });

            let html = `
            <tr data-row-index="${rowIndex}">
                <td class="text-center nomor"></td>

                <td>
                    <select name="details[${rowIndex}][product_id]" class="form-control select2 product">
                        ${productOptions}
                    </select>
                </td>

                <td>
                    <input type="number" step="0.01" min="0"
                        name="details[${rowIndex}][total_kg]" class="form-control total-kg">
                </td>

                <td class="text-right align-middle">
                    <span class="harga-per-kg">-</span>
                </td>

                <td class="text-right align-middle">
                    <strong class="rupiah">-</strong>
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm removeRow">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

            $('#detailTable tbody').append(html);

            $('#detailTable tbody tr:last .select2').select2({
                width: '100%'
            });

            renumberRows();
            rowIndex++;
        });

        function renumberRows() {
            $('#detailTable tbody tr').each(function(index) {
                $(this).find('.nomor').text(index + 1);
            });
        }
    </script>
@endpush
