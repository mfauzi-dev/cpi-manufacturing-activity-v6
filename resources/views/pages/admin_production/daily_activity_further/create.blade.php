@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Input Daily Activity Further</h1>
    </div>

    <div class="section-body">

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

        <form action="{{ route('admin-production.daily-activity-further.store') }}" method="POST" id="dailyActivityForm">

            @csrf

            <div class="card">
                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal</label>

                                <input type="date" name="tanggal" id="tanggal"
                                    class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', date('Y-m-d')) }}">

                                @error('tanggal')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
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

                                    <option value="">
                                        Pilih Cost Center
                                    </option>

                                    @foreach ($costCenterList as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->code }} - {{ $costCenter->name }}
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

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Process Type</label>

                                <select name="process_type_id" id="process_type"
                                    class="form-control @error('process_type_id') is-invalid @enderror">

                                    <option value="">
                                        Pilih Process Type
                                    </option>

                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}"
                                            {{ old('process_type_id') == $processType->id ? 'selected' : '' }}>
                                            {{ $processType->name }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('process_type_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

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

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Line</label>
                                <select name="line_id" id="line_id"
                                    class="form-control @error('line_id') is-invalid @enderror">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->id }}"
                                            {{ old('line_id') == $line->id ? 'selected' : '' }}>
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

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nama Karyawan</label>

                                <select id="employee_id_group" class="form-control select2" multiple>
                                </select>

                                <small class="text-muted">
                                    Pilih satu atau beberapa karyawan.
                                </small>

                                @error('employees')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Total Man Hours</label>

                                <input type="text" id="total_man_hours" class="form-control" value="0,00" readonly>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="card">

                <div class="card-header">
                    <h4>Detail Daily Activity Further</h4>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered" id="detailTable" style="table-layout: fixed;">

                        <thead class="text-center">

                            <tr>

                                <th rowspan="2" width="70">
                                    No
                                </th>

                                <th rowspan="2" width="350">
                                    Nama Material
                                </th>

                                <th colspan="2" width="300">
                                    Production
                                </th>

                                <th rowspan="2" width="150">
                                    Total KG
                                </th>

                                <th rowspan="2" width="150">
                                    Productivity
                                </th>

                                <th rowspan="2" width="80">
                                    Aksi
                                </th>

                            </tr>

                            <tr>

                                <th width="150">
                                    RM
                                </th>

                                <th width="150">
                                    FG
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr data-row-index="0">

                                <td class="text-center nomor">
                                    1
                                </td>

                                <td>

                                    <select name="details[0][product_id]" class="form-control select2 product">

                                        <option value="">
                                            Pilih Nama Material
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <input type="number" step="0.01" min="0" name="details[0][total_kg_rm]"
                                        class="form-control total-kg-rm">

                                </td>

                                <td>

                                    <input type="number" step="0.01" min="0" name="details[0][total_kg_fg]"
                                        class="form-control total-kg-fg">

                                </td>

                                <td class="text-right align-middle">

                                    <span class="total-kg">
                                        0,00
                                    </span>

                                </td>

                                <td class="text-right align-middle">

                                    <span class="productivity">
                                        -
                                    </span>

                                </td>

                                <td class="text-center">

                                    <button type="button" class="btn btn-danger btn-sm removeRow">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                        <tfoot>

                            <tr>

                                <th colspan="4" class="text-right">

                                    Total FG / Total KG

                                </th>

                                <th class="text-right">

                                    <span id="grand-total-kg">
                                        0,00
                                    </span>

                                </th>

                                <th class="text-right">

                                    <span id="grand-productivity">
                                        -
                                    </span>

                                </th>

                                <th></th>

                            </tr>

                        </tfoot>

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


@push('addon-style')
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

        #employee_id_group+.select2-container {
            width: 100% !important;
        }
    </style>
@endpush


@push('scripts')
    <script>
        let rowIndex = 1;
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
            if ($('#cost_center').val()) {
                loadCostCenterDependents($('#cost_center').val());
            }
            if ($('#cost_center').val() && $('#process_type').val()) {
                loadProducts();
            }
        });
        $('#cost_center').change(function() {
            let costCenterId = $(this).val();
            $('#process_type').val('');
            $('#ps_group').html('<option value="">Pilih Group</option>');
            resetEmployeeSelect();
            $('.product').html('<option value="">Pilih Nama Material</option>');
            if (!costCenterId) {
                resetCalculation();
                return;
            }
            loadCostCenterDependents(costCenterId);
        });
        $('#process_type').change(function() {
            $('.product').html('<option value="">Pilih Nama Material</option>');
            loadProducts();
        });

        function loadProducts() {
            let costCenterId = $('#cost_center').val();
            let processTypeId = $('#process_type').val();
            let options = '<option value="">Pilih Nama Material</option>';
            if (!costCenterId || !processTypeId) {
                $('.product').each(function() {
                    $(this).html(options).trigger('change');
                });
                return;
            }
            $.ajax({
                url: "{{ route('admin-production.daily-activity-further.products', ':id') }}".replace(':id',
                    costCenterId),
                type: 'GET',
                data: {
                    process_type_id: processTypeId
                },
                success: function(res) {
                    $.each(res, function(i, item) {
                        let codePart = '';
                        if (item.material_code) {
                            codePart = ' - ' + item.material_code;
                        }
                        options +=
                            ` <option value="${item.id}"> ${item.material_name}${codePart} </option> `;
                    });
                    $('.product').each(function() {
                        let selectedValue = $(this).val();
                        $(this).html(options);
                        if (selectedValue) {
                            $(this).val(selectedValue);
                        }
                        $(this).trigger('change');
                    });
                },
                error: function() {
                    $('.product').each(function() {
                        $(this).html('<option value="">Gagal mengambil product</option>').trigger(
                            'change');
                    });
                }
            });
        }

        function loadCostCenterDependents(costCenterId) {
            $.get("{{ route('daily-activity-further.ps-groups', ':id') }}".replace(':id', costCenterId), function(data) {
                let html = '<option value="">Pilih Group</option>';
                let oldPsGroup = "{{ old('ps_group_id') }}";
                $.each(data, function(i, item) {
                    let selected = '';
                    if (oldPsGroup && oldPsGroup == item.id) {
                        selected = 'selected';
                    }
                    html += ` <option value="${item.id}" ${selected}> ${item.name} </option> `;
                });
                $('#ps_group').html(html);
                if ($('#ps_group').val()) {
                    loadEmployees(costCenterId, $('#ps_group').val());
                }
            });
        }
        $('#ps_group').change(function() {
            let costCenterId = $('#cost_center').val();
            let psGroupId = $(this).val();
            resetEmployeeSelect();
            if (!costCenterId || !psGroupId) {
                return;
            }
            loadEmployees(costCenterId, psGroupId);
        });

        function loadEmployees(costCenterId, psGroupId) {
            $.get("{{ route('daily-activity-further.employees', [':costCenterId', ':psGroupId']) }}".replace(
                ':costCenterId', costCenterId).replace(':psGroupId', psGroupId), function(data) {
                let options = '';
                $.each(data, function(i, item) {
                    let employeeText = '';
                    if (item.nik) {
                        employeeText = item.nik + ' - ' + item.name;
                    } else {
                        employeeText = item.name;
                    }
                    options += ` <option value="${item.id}"> ${employeeText} </option> `;
                });
                $('#employee_id_group').html(options).val(null).trigger('change');
            });
        }
        $('#employee_id_group').change(function() {
            let employeeIds = $(this).val() || [];
            updateManHours(employeeIds);
        });
        $('#tanggal').change(function() {
            let employeeIds = $('#employee_id_group').val() || [];
            if (employeeIds.length > 0) {
                updateManHours(employeeIds);
            } else {
                $('#total_man_hours').val('0,00');
                updateProductivity(0);
            }
        });

        function updateManHours(employeeIds) {
            let tanggal = $('#tanggal').val();
            if (!tanggal || employeeIds.length === 0) {
                $('#total_man_hours').val('0,00');
                updateProductivity(0);
                return;
            }
            $.ajax({
                url: "{{ route('admin-production.daily-activity-further.man-hours') }}",
                type: 'GET',
                data: {
                    tanggal: tanggal,
                    employee_ids: employeeIds
                },
                success: function(response) {
                    let manHours = parseFloat(response.man_hours) || 0;
                    $('#total_man_hours').val(formatNumber(manHours));
                    updateProductivity(manHours);
                },
                error: function() {
                    $('#total_man_hours').val('0,00');
                    updateProductivity(0);
                }
            });
        }

        function updateProductivity(manHours) {
            let totalFg = 0;
            $('#detailTable tbody tr').each(function() {
                let fg = parseFloat($(this).find('.total-kg-fg').val()) || 0;
                totalFg += fg;
            });
            $('#grand-total-kg').text(formatNumber(totalFg));
            let productivity = manHours > 0 ? totalFg / manHours : 0;
            if (manHours > 0 && totalFg > 0) {
                $('#grand-productivity').text(formatNumber(productivity));
            } else {
                $('#grand-productivity').text('-');
            }
            $('#detailTable tbody tr').each(function() {
                let row = $(this);
                let fg = parseFloat(row.find('.total-kg-fg').val()) || 0;
                row.find('.total-kg').text(formatNumber(fg));
                if (manHours > 0 && totalFg > 0) {
                    row.find('.productivity').text(formatNumber(productivity));
                } else {
                    row.find('.productivity').text('-');
                }
            });
        }

        function getManHours() {
            let value = $('#total_man_hours').val();
            if (!value) {
                return 0;
            }
            value = value.replace(/\./g, '').replace(',', '.');
            return parseFloat(value) || 0;
        }

        function formatNumber(value) {
            return Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        $('#btnAddRow').click(function() {
            let productOptions = '';
            $('.product:first option').each(function() {
                productOptions += ` <option value="${$(this).val()}"> ${$(this).text()} </option> `;
            });
            let html =
                ` <tr data-row-index="${rowIndex}"> <td class="text-center nomor"> </td> <td> <select name="details[${rowIndex}][product_id]" class="form-control select2 product"> ${productOptions} </select> </td> <td> <input type="number" step="0.01" min="0" name="details[${rowIndex}][total_kg_rm]" class="form-control total-kg-rm"> </td> <td> <input type="number" step="0.01" min="0" name="details[${rowIndex}][total_kg_fg]" class="form-control total-kg-fg"> </td> <td class="text-right align-middle"> <span class="total-kg"> 0,00 </span> </td> <td class="text-right align-middle"> <span class="productivity"> - </span> </td> <td class="text-center"> <button type="button" class="btn btn-danger btn-sm removeRow"> <i class="fas fa-trash"></i> </button> </td> </tr> `;
            $('#detailTable tbody').append(html);
            $('#detailTable tbody tr:last .select2').select2({
                width: '100%'
            });
            renumberRows();
            rowIndex++;
            updateProductivity(getManHours());
        });
        $(document).on('keyup change', '.total-kg-fg', function() {
            updateProductivity(getManHours());
        });
        $(document).on('click', '.removeRow', function() {
            if ($('#detailTable tbody tr').length <= 1) {
                alert('Minimal harus ada 1 baris.');
                return;
            }
            $(this).closest('tr').remove();
            renumberRows();
            updateProductivity(getManHours());
        });

        function renumberRows() {
            $('#detailTable tbody tr').each(function(index) {
                $(this).find('.nomor').text(index + 1);
            });
        }

        function resetEmployeeSelect() {
            $('#employee_id_group').html('').val(null).trigger('change');
            $('#total_man_hours').val('0,00');
            updateProductivity(0);
        }

        function resetCalculation() {
            $('#grand-total-kg').text('0,00');
            $('#grand-productivity').text('-');
            $('#detailTable tbody tr').each(function() {
                $(this).find('.total-kg').text('0,00');
                $(this).find('.productivity').text('-');
            });
        }
        $('#dailyActivityForm').submit(function(e) {
            let employeeIds = $('#employee_id_group').val() || [];
            if (employeeIds.length === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 employee.');
                return false;
            }
            let costCenter = $('#cost_center').val();
            let processType = $('#process_type').val();
            let psGroup = $('#ps_group').val();
            if (!costCenter) {
                e.preventDefault();
                alert('Cost Center wajib dipilih.');
                return false;
            }
            if (!processType) {
                e.preventDefault();
                alert('Process Type wajib dipilih.');
                return false;
            }
            if (!psGroup) {
                e.preventDefault();
                alert('Group wajib dipilih.');
                return false;
            }
            $('.hidden-employee').remove();
            employeeIds.forEach(function(employeeId, index) {
                $('<input>').attr({
                    type: 'hidden',
                    name: `employees[${index}][employee_id]`,
                    value: employeeId,
                    class: 'hidden-employee'
                }).appendTo('#dailyActivityForm');
            });
            let productRows = $('#detailTable tbody tr');
            if (productRows.length === 0) {
                e.preventDefault();
                alert('Minimal harus ada 1 product.');
                return false;
            }
            let validProduct = false;
            productRows.each(function() {
                let productId = $(this).find('.product').val();
                if (productId) {
                    validProduct = true;
                }
            });
            if (!validProduct) {
                e.preventDefault();
                alert('Pilih minimal 1 product.');
                return false;
            }
        });
    </script>
@endpush
