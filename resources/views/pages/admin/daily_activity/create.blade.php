@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Input Daily Activity</h1>
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

        <form action="{{ route('admin-production.daily-activity.store') }}" method="POST">

            @csrf

            <div class="card">

                <div class="card-body">

                    <div class="row">

                        {{-- Tanggal --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tanggal</label>

                                <input type="date" name="tanggal" class="form-control"
                                    value="{{ old('tanggal', date('Y-m-d')) }}">
                            </div>
                        </div>

                        {{-- Department --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Department</label>

                                <select id="department" name="department_id" class="form-control">

                                    <option value="">Pilih Department</option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">

                                            {{ $department->name }}

                                        </option>
                                    @endforeach

                                </select>

                            </div>
                        </div>

                        {{-- Cost Center --}}
                        <div class="col-md-2">
                            <div class="form-group">

                                <label>Cost Center</label>

                                <select id="cost_center" name="cost_center_id" class="form-control">

                                    <option value="">Pilih Cost Center</option>

                                </select>

                            </div>
                        </div>

                        {{-- PS GROUP --}}
                        <div class="col-md-2">
                            <div class="form-group">

                                <label>PS Group</label>

                                <select id="ps_group" name="ps_group_id" class="form-control">

                                    <option value="">Pilih PS Group</option>

                                </select>

                            </div>
                        </div>

                        {{-- Employee --}}
                        <div class="col-md-4">

                            <div class="form-group">

                                <label>Nama Input</label>

                                <select id="employee" name="employee_id" class="form-control select2">

                                    <option value="">Pilih Employee</option>

                                </select>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- DETAIL --}}
            <div class="card">

                <div class="card-header">

                    <h4>Detail Daily Activity</h4>

                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered" id="detailTable">
                        <thead class="text-center">
                            <tr>
                                <th width="60">No</th>
                                <th>Nama SKU</th>
                                <th width="170">Output PAC</th>
                                <th width="170">Output KG</th>
                                <th width="170">Harga / KG</th>
                                <th width="170">Rupiah</th>
                                <th width="60">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>

                                <td class="text-center nomor">

                                    1

                                </td>

                                <td>

                                    <select name="details[0][product_id]" class="form-control select2 product">

                                        <option value="">
                                            Pilih SKU
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <input type="number" name="details[0][output_pac]" class="form-control output-pac"
                                        min="0">

                                </td>

                                <td>

                                    <input type="number" step="0.01" name="details[0][output_kg]"
                                        class="form-control output-kg" min="0">

                                </td>
                                <td class="text-right align-middle">
                                    <span class="harga-per-kg">
                                        -
                                    </span>
                                </td>

                                <td class="text-right align-middle">
                                    <strong class="rupiah">
                                        -
                                    </strong>
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

@push('scripts')
    {{-- <script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script> --}}

    <script>
        $(function() {

            $('.select2').select2({
                width: '100%'
            });

        });

        loadDepartmentData();

        loadCostCenterData();

        function loadDepartmentData() {

            $('#department').change(function() {

                let departmentId = $(this).val();

                if (!departmentId) {
                    return;
                }

                // Cost Center
                $.get(
                    "{{ route('admin-production.daily-activity.cost-centers', ':id') }}"
                    .replace(':id', departmentId),

                    function(res) {

                        let html =
                            '<option value="">Pilih Cost Center</option>';

                        $.each(res, function(i, item) {

                            html +=
                                `<option value="${item.id}">${item.name}</option>`;

                        });

                        $('#cost_center').html(html);

                    }
                );

                // Product
                $.get(
                    "{{ route('admin-production.daily-activity.products', ':id') }}"
                    .replace(':id', departmentId),

                    function(res) {

                        let options =
                            '<option value="">Pilih SKU</option>';

                        $.each(res, function(i, item) {

                            options += `
                        <option
                            value="${item.id}"
                            data-price="${item.harga_per_kg}">
                            ${item.nama_sku}
                        </option>
                    `;

                        });

                        $('.product').html(options);

                    }
                );

            });

        }

        function loadCostCenterData() {

            $('#cost_center').change(function() {

                let costCenterId = $(this).val();

                if (!costCenterId) {
                    return;
                }

                // PS GROUP
                $.get(
                    "{{ route('admin-production.daily-activity.ps-groups', ':id') }}"
                    .replace(':id', costCenterId),

                    function(res) {

                        let html =
                            '<option value="">Pilih PS Group</option>';

                        $.each(res, function(i, item) {

                            html +=
                                `<option value="${item.id}">${item.name}</option>`;

                        });

                        $('#ps_group').html(html);

                    }
                );

                // EMPLOYEE
                $.get(
                    "{{ route('admin-production.daily-activity.employees', ':id') }}"
                    .replace(':id', costCenterId),

                    function(res) {

                        let html =
                            '<option value="">Pilih Employee</option>';

                        $.each(res, function(i, item) {

                            html += `
                        <option value="${item.id}">
                            ${item.nik} - ${item.name}
                        </option>
                    `;

                        });

                        $('#employee').html(html);

                    }
                );

            });

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

            let harga = $(this)
                .find(':selected')
                .data('price') || 0;

            row.find('.harga-per-kg')
                .text('Rp ' + Number(harga).toLocaleString('id-ID'));

            hitungRupiah(row);

        });

        $(document).on('keyup change', '.output-kg', function() {

            hitungRupiah($(this).closest('tr'));

        });

        function hitungRupiah(row) {

            let kg = parseFloat(
                row.find('.output-kg').val()
            ) || 0;

            let harga = row
                .find('.product option:selected')
                .data('price') || 0;

            let total = kg * harga;

            row.find('.rupiah')
                .text(
                    'Rp ' +
                    Number(total).toLocaleString('id-ID')
                );

        }

        let rowIndex = 1;

        $('#btnAddRow').click(function() {

            let option = '';

            $('.product:first option').each(function() {

                option += `
            <option
                value="${$(this).val()}"
                data-price="${$(this).data('price') ?? ''}">
                ${$(this).text()}
            </option>
        `;

            });

            let html = `

        <tr>

            <td class="text-center nomor"></td>

            <td>

                <select
                    name="details[${rowIndex}][product_id]"
                    class="form-control select2 product">

                    ${option}

                </select>

            </td>

            <td>

                <input
                    type="number"
                    min="0"
                    name="details[${rowIndex}][output_pac]"
                    class="form-control output-pac">

            </td>

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="details[${rowIndex}][output_kg]"
                    class="form-control output-kg">

            </td>

            <td class="text-right align-middle">

                <span class="harga-per-kg">

                    -

                </span>

            </td>

            <td class="text-right align-middle">

                <strong class="rupiah">

                    -

                </strong>

            </td>

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-danger btn-sm removeRow">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        </tr>

    `;

            $('#detailTable tbody').append(html);

            $('#detailTable tbody tr:last .select2').select2({
                width: '100%'
            });

            renumberRows();

            rowIndex++;

        });


        function renumberRows() {

            $('#detailTable tbody tr').each(function(index) {

                $(this)
                    .find('.nomor')
                    .text(index + 1);

            });

        }
    </script>
@endpush
