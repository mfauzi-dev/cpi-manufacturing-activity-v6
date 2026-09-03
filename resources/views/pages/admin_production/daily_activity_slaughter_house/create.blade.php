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
                <button class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin-production.daily-activity-slaughter-house.store') }}" method="POST"
            id="dailyActivityForm">

            @csrf

            {{-- HEADER --}}
            <div class="card">
                <div class="card-body">

                    <div class="row">

                        {{-- TANGGAL --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal</label>

                                <input type="date" name="tanggal" class="form-control"
                                    value="{{ old('tanggal', date('Y-m-d')) }}">
                            </div>
                        </div>

                        {{-- DEPARTMENT --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Department</label>

                                <input type="text" class="form-control" value="{{ $department->name }}" readonly>

                                <input type="hidden" id="department_id" value="{{ $department->id }}">

                            </div>
                        </div>

                        {{-- COST CENTER --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Cost Center</label>

                                <select name="cost_center_id" id="cost_center"
                                    class="form-control @error('cost_center_id') is-invalid @enderror">

                                    <option value="">
                                        Pilih Cost Center
                                    </option>

                                    @foreach ($costCenterList as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>

                                            {{ $costCenter->code }} -
                                            {{ $costCenter->name }}

                                        </option>
                                    @endforeach

                                </select>

                                @error('cost_center_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>
                        </div>

                        {{-- GROUP --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Group</label>

                                <select name="ps_group_id" id="ps_group"
                                    class="form-control @error('ps_group_id') is-invalid @enderror">

                                    <option value="">
                                        Pilih Group
                                    </option>

                                </select>

                                @error('ps_group_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>
                        </div>

                        {{-- LINE --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Line</label>

                                <select name="line_id" id="line_id"
                                    class="form-control @error('line_id') is-invalid @enderror">

                                    <option value="">
                                        Pilih Line
                                    </option>

                                    @foreach ($lineList as $line)
                                        <option value="{{ $line->id }}"
                                            {{ old('line_id') == $line->id ? 'selected' : '' }}>

                                            {{ $line->code ? $line->code . ' - ' : '' }}
                                            {{ $line->name }}

                                        </option>
                                    @endforeach

                                </select>

                                @error('line_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>
                        </div>

                        {{-- EMPLOYEE --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Nama Karyawan</label>

                                <select id="employee_id_group" multiple class="form-control select2">

                                    @foreach ($employeeList as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ collect(old('employee_id', []))->contains($employee->id) ? 'selected' : '' }}>

                                            {{ $employee->name }} - {{ $employee->outsourcing?->name ?? '-' }}

                                        </option>
                                    @endforeach

                                </select>

                                @error('details.*.employee_id')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

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

                    <table class="table table-bordered" id="detailTable" style="table-layout: fixed;">

                        <thead class="text-center">

                            <tr>

                                <th width="70">
                                    No
                                </th>

                                <th width="350">
                                    Nama Material
                                </th>

                                <th width="150">
                                    Output KG
                                </th>

                                <th width="150">
                                    Lama Packing
                                </th>

                                <th width="130">
                                    Productivity
                                </th>

                                <th width="170">
                                    Harga / KG
                                </th>

                                <th width="180">
                                    Rupiah
                                </th>

                                <th width="100">
                                    Aksi
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr data-row-index="0">

                                <td class="text-center nomor">
                                    1
                                </td>

                                {{-- PRODUCT --}}
                                <td>

                                    <select name="details[0][product_id]" class="form-control select2 product">

                                        <option value="">
                                            Pilih Nama Material
                                        </option>

                                    </select>

                                </td>

                                {{-- OUTPUT --}}
                                <td>

                                    <input type="number" step="0.01" min="0" name="details[0][output_kg]"
                                        class="form-control output-kg">

                                </td>

                                {{-- LAMA PACKING --}}
                                <td>

                                    <input type="number" step="0.01" min="0" name="details[0][lama_packing]"
                                        class="form-control lama-packing">

                                </td>

                                {{-- PRODUCTIVITY --}}
                                <td class="text-right align-middle">

                                    <span class="productivity">
                                        -
                                    </span>

                                </td>

                                {{-- HARGA --}}
                                <td class="text-right align-middle">

                                    <span class="harga-per-kg">
                                        -
                                    </span>

                                </td>

                                {{-- RUPIAH --}}
                                <td class="text-right align-middle">

                                    <strong class="rupiah">
                                        -
                                    </strong>

                                </td>

                                {{-- AKSI --}}
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
        $(document).ready(function() {

            /*
            |--------------------------------------------------------------------------
            | SELECT2
            |--------------------------------------------------------------------------
            */

            $('.select2').select2({
                width: '100%'
            });

            let costCenterId = $('#cost_center').val();

            if (costCenterId) {

                loadPsGroups(costCenterId);
                loadProducts(costCenterId);

            }

            $('#cost_center').change(function() {

                let costCenterId = $(this).val();

                $('#ps_group').html(
                    '<option value="">Pilih Group</option>'
                );

                $('.product').html(
                    '<option value="">Pilih Nama Material</option>'
                );

                resetProductInfo();

                if (!costCenterId) {
                    return;
                }

                loadPsGroups(costCenterId);
                loadProducts(costCenterId);

            });


            function loadPsGroups(costCenterId) {

                $.get(

                    "{{ route('daily-activity-slaughter-house.ps-groups', ':id') }}"
                    .replace(':id', costCenterId),

                    function(data) {

                        let html =
                            '<option value="">Pilih Group</option>';

                        let oldPsGroup =
                            "{{ old('ps_group_id') }}";

                        $.each(data, function(i, item) {

                            let selected =
                                oldPsGroup &&
                                oldPsGroup == item.id ?
                                'selected' :
                                '';

                            html += `

                        <option value="${item.id}" ${selected}>

                            ${item.name}

                        </option>

                    `;

                        });

                        $('#ps_group').html(html);

                    }

                );

            }



            function loadProducts(costCenterId) {

                $.get(

                    "{{ route('daily-activity-slaughter-house.products', ':id') }}"
                    .replace(':id', costCenterId),

                    function(data) {

                        let options =
                            '<option value="">Pilih Nama Material</option>';

                        $.each(data, function(i, item) {

                            let price =
                                item.harga_per_kg ?? 0;

                            let codePart =
                                item.material_code ?
                                ' - ' + item.material_code :
                                '';

                            options += `

                        <option
                            value="${item.id}"
                            data-price="${price}">

                            ${item.material_name}${codePart}

                        </option>

                    `;

                        });

                        $('.product').html(options);

                        resetProductInfo();

                    }

                );

            }



            $(document).on(
                'change',
                '.product',
                function() {

                    let row =
                        $(this).closest('tr');

                    let harga =
                        $(this)
                        .find(':selected')
                        .data('price') || 0;

                    row.find('.harga-per-kg')
                        .text(
                            'Rp ' +
                            Number(harga)
                            .toLocaleString('id-ID')
                        );

                    hitungRupiah(row);

                }
            );



            $(document).on(
                'keyup change',
                '.output-kg, .lama-packing',
                function() {

                    hitungRupiah(
                        $(this).closest('tr')
                    );

                }
            );


            function hitungRupiah(row) {

                let kg =
                    parseFloat(
                        row.find('.output-kg').val()
                    ) || 0;

                let lamaPacking =
                    parseFloat(
                        row.find('.lama-packing').val()
                    ) || 0;

                let harga =
                    row.find('.product option:selected')
                    .data('price') || 0;



                let total =
                    kg * harga;

                row.find('.rupiah')
                    .text(
                        'Rp ' +
                        Number(total)
                        .toLocaleString('id-ID')
                    );


                let productivityEl =
                    row.find('.productivity');

                if (lamaPacking > 0) {

                    let productivity =
                        kg / lamaPacking;

                    productivityEl.text(

                        productivity.toLocaleString(
                            'id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        )

                    );

                } else {

                    productivityEl.text('-');

                }

            }



            function resetProductInfo() {

                $('#detailTable tbody tr')
                    .each(function() {

                        $(this)
                            .find('.harga-per-kg')
                            .text('-');

                        $(this)
                            .find('.rupiah')
                            .text('-');

                        $(this)
                            .find('.productivity')
                            .text('-');

                    });

            }



            let rowIndex = 1;

            $('#btnAddRow').click(function() {

                let productOptions = '';

                $('.product:first option')
                    .each(function() {

                        productOptions += `

                    <option
                        value="${$(this).val()}"
                        data-price="${$(this).data('price') ?? ''}">

                        ${$(this).text()}

                    </option>

                `;

                    });


                let html = `

            <tr data-row-index="${rowIndex}">

                <td class="text-center nomor">
                </td>

                <td>

                    <select
                        name="details[${rowIndex}][product_id]"
                        class="form-control select2 product">

                        ${productOptions}

                    </select>

                </td>

                <td>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="details[${rowIndex}][output_kg]"
                        class="form-control output-kg">

                </td>

                <td>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="details[${rowIndex}][lama_packing]"
                        class="form-control lama-packing">

                </td>

                <td class="text-right align-middle">

                    <span class="productivity">
                        -
                    </span>

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


                $('#detailTable tbody')
                    .append(html);


                $('#detailTable tbody tr:last .select2')
                    .select2({
                        width: '100%'
                    });


                renumberRows();

                rowIndex++;

            });


            $(document).on(
                'click',
                '.removeRow',
                function() {

                    if (
                        $('#detailTable tbody tr').length <= 1
                    ) {

                        alert(
                            'Minimal harus ada 1 baris.'
                        );

                        return;

                    }

                    $(this)
                        .closest('tr')
                        .remove();

                    renumberRows();

                }
            );


            function renumberRows() {

                $('#detailTable tbody tr')
                    .each(function(index) {

                        $(this)
                            .find('.nomor')
                            .text(index + 1);

                    });

            }

            $('#dailyActivityForm').on(
                'submit',
                function(e) {

                    let employeeIds =
                        $('#employee_id_group').val() || [];


                    if (employeeIds.length === 0) {

                        e.preventDefault();

                        alert(
                            'Pilih minimal 1 employee.'
                        );

                        return false;

                    }


                    $('#detailTable tbody tr')
                        .each(function() {

                            let row =
                                $(this);

                            let idx =
                                row.data('row-index');


                            row.find(
                                'input.hidden-employee'
                            ).remove();


                            employeeIds.forEach(
                                function(empId) {

                                    $('<input>')
                                        .attr({

                                            type: 'hidden',

                                            class: 'hidden-employee',

                                            name: `details[${idx}][employee_id][]`,

                                            value: empId

                                        })
                                        .appendTo(row);

                                }
                            );

                        });

                }
            );

        });
    </script>
@endpush
