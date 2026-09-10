@extends('layouts.master')

@section('content')

    <div class="section-header">
        <div>
            <a href="{{ route('admin-production.daily-activity-further.index') }}" class="text-dark mr-2">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <h1 class="d-inline">
            {{ $costCenter->code }} - {{ $costCenter->name }}
        </h1>

        <div>
            @if ($line)
                <span class="badge badge-info ml-2">
                    Line: {{ $line->code ? $line->code . ' - ' : '' }}{{ $line->name }}
                </span>
            @endif
        </div>
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

    <div class="section-body">

        <div class="card mb-3">
            <div class="card-body">

                <form method="GET"
                    action="{{ route('admin-production.daily-activity-further.detail', [
                        'costCenter' => $costCenter->id,
                        'psGroup' => $psGroup->id,
                        'lineId' => $line->id ?? null,
                    ]) }}">

                    <div class="row align-items-end">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('admin-production.daily-activity-further.detail', [
                            'costCenter' => $costCenter->id,
                            'psGroup' => $psGroup->id,
                            'lineId' => $line->id ?? null,
                        ]) }}"
                            class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>

            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">

                <div class="mb-3 d-flex align-items-center">

                    <a href="{{ route('admin-production.daily-activity-further.export-excel', [
                        'costCenterId' => $costCenter->id,
                        'psGroupId' => $psGroup->id,
                        'line_id' => $line->id ?? null,
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                    ]) }}"
                        class="btn btn-success mr-2">

                        <i class="fas fa-file-excel"></i>
                        Excel

                    </a>

                    <a href="{{ route('admin-production.daily-activity-further.export-pdf', [
                        'costCenterId' => $costCenter->id,
                        'psGroupId' => $psGroup->id,
                        'line_id' => $line->id ?? null,
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                    ]) }}"
                        class="btn btn-danger mr-2" target="_blank">

                        <i class="fas fa-file-pdf"></i>
                        PDF

                    </a>

                    <form id="bulkDeleteForm" action="{{ route('admin-production.daily-activity-further.bulk-destroy') }}"
                        method="POST" class="d-inline">

                        @csrf
                        @method('DELETE')

                        <div id="bulkDeleteInputs"></div>

                        <button type="submit" class="btn btn-danger" id="btnBulkDelete" disabled>

                            <i class="fas fa-trash"></i>
                            Hapus Terpilih

                        </button>

                    </form>

                </div>

                @php
                    $groupedDetails = [];

                    foreach ($details as $detail) {
                        $groupKey = $detail->tanggal . '_' . $detail->line_id;

                        if (!isset($groupedDetails[$groupKey])) {
                            $groupedDetails[$groupKey] = [
                                'tanggal' => $detail->tanggal,
                                'line_name' => $detail->line_name,
                                'line_id' => $detail->line_id,
                                'employees' => [],
                                'products' => [],
                            ];
                        }

                        if (!isset($groupedDetails[$groupKey]['employees'][$detail->employee_id])) {
                            $groupedDetails[$groupKey]['employees'][$detail->employee_id] = [
                                'man_power' => (float) $detail->man_power,
                            ];
                        }

                        $productKey = $detail->product_id;

                        if (!isset($groupedDetails[$groupKey]['products'][$productKey])) {
                            $groupedDetails[$groupKey]['products'][$productKey] = [
                                'product_id' => $detail->product_id,
                                'material_code' => $detail->material_code,
                                'material_name' => $detail->material_name,
                                'total_kg_rm' => 0,
                                'total_kg_fg' => 0,
                                'detail_ids' => [],
                            ];
                        }

                        $groupedDetails[$groupKey]['products'][$productKey]['total_kg_rm'] +=
                            (float) $detail->total_kg_rm;

                        $groupedDetails[$groupKey]['products'][$productKey]['total_kg_fg'] +=
                            (float) $detail->total_kg_fg;

                        $groupedDetails[$groupKey]['products'][$productKey]['detail_ids'][] = $detail->id;
                    }
                @endphp

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th rowspan="2" class="text-center align-middle">

                                <input type="checkbox" id="checkAll">

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Tanggal

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Line

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Product

                            </th>

                            <th colspan="2" class="text-center">

                                Production

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Total KG

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Man Hours

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Productivity

                            </th>

                            <th rowspan="2" class="text-center align-middle">

                                Action

                            </th>

                        </tr>

                        <tr>

                            <th class="text-center">
                                RM
                            </th>

                            <th class="text-center">
                                FG
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($groupedDetails as $group)
                            @php

                                $manHours = 0;

                                foreach ($group['employees'] as $employeeData) {
                                    $manHours += (float) $employeeData['man_power'];
                                }

                                $productCount = count($group['products']);

                                $firstProduct = true;

                                /*
                                 * TOTAL KG UNTUK PRODUCTIVITY
                                 * MENGGUNAKAN TOTAL FG
                                 */
                                $grandTotalKg = 0;

                                foreach ($group['products'] as $item) {
                                    $grandTotalKg += (float) $item['total_kg_fg'];
                                }

                                /*
                                 * PRODUCTIVITY
                                 * TOTAL FG / MAN HOURS
                                 */
                                $groupProductivity = $manHours > 0 ? $grandTotalKg / $manHours : 0;

                            @endphp

                            @foreach ($group['products'] as $product)
                                @php

                                    $totalKgFg = (float) $product['total_kg_fg'];

                                    $detailId = end($product['detail_ids']);

                                @endphp

                                <tr>

                                    <td class="text-center align-middle">

                                        <input type="checkbox" class="detail-checkbox"
                                            value="{{ implode(',', $product['detail_ids']) }}">

                                    </td>

                                    @if ($firstProduct)
                                        <td rowspan="{{ $productCount }}" class="align-middle">

                                            {{ \Carbon\Carbon::parse($group['tanggal'])->translatedFormat('d M Y') }}

                                        </td>

                                        <td rowspan="{{ $productCount }}" class="align-middle">

                                            {{ $group['line_name'] ?? '-' }}

                                        </td>
                                    @endif

                                    <td>

                                        <div>
                                            {{ $product['material_name'] }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $product['material_code'] ?? '-' }}
                                        </small>

                                    </td>

                                    <td class="text-right">

                                        {{ number_format($product['total_kg_rm'], 2, ',', '.') }}

                                    </td>

                                    <td class="text-right">

                                        {{ number_format($product['total_kg_fg'], 2, ',', '.') }}

                                    </td>

                                    @if ($firstProduct)
                                        <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                            {{ number_format($grandTotalKg, 2, ',', '.') }}

                                        </td>

                                        <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                            {{ number_format($manHours, 2, ',', '.') }}

                                        </td>

                                        <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                            {{ number_format($groupProductivity, 2, ',', '.') }}

                                        </td>
                                    @endif

                                    <td class="text-center">

                                        <div class="btn-group">

                                            <a href="{{ route('admin-production.daily-activity-further.edit', $detailId) }}"
                                                class="btn btn-warning btn-sm mr-2">

                                                Edit

                                            </a>

                                            <form
                                                action="{{ route('admin-production.daily-activity-further.destroy', $detailId) }}"
                                                method="POST" onsubmit="return confirm('Yakin hapus data ini?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-danger">

                                                    Hapus

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                                @php
                                    $firstProduct = false;
                                @endphp
                            @endforeach

                        @empty

                            <tr>

                                <td colspan="10" class="text-center">

                                    Tidak ada data pada rentang tanggal ini

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

                <div class="card-footer text-right">

                    {{ $details->withQueryString()->links() }}

                </div>

            </div>
        </div>

    </div>


@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const checkAll = document.getElementById('checkAll');
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const bulkDeleteInputs = document.getElementById('bulkDeleteInputs');

            function getCheckboxes() {
                return document.querySelectorAll('.detail-checkbox');
            }

            function getCheckedCheckboxes() {
                return document.querySelectorAll('.detail-checkbox:checked');
            }

            function updateBulkButton() {

                const checkboxes = getCheckboxes();
                const checked = getCheckedCheckboxes();

                btnBulkDelete.disabled = checked.length === 0;

                if (checkboxes.length === 0) {

                    checkAll.checked = false;
                    checkAll.indeterminate = false;

                    return;
                }

                checkAll.checked =
                    checked.length === checkboxes.length;

                checkAll.indeterminate =
                    checked.length > 0 &&
                    checked.length < checkboxes.length;
            }

            checkAll.addEventListener('change', function() {

                getCheckboxes().forEach(function(checkbox) {
                    checkbox.checked = checkAll.checked;
                });

                updateBulkButton();

            });

            document.addEventListener('change', function(event) {

                if (event.target.classList.contains('detail-checkbox')) {
                    updateBulkButton();
                }

            });

            bulkDeleteForm.addEventListener('submit', function(event) {

                const checked = getCheckedCheckboxes();

                if (checked.length === 0) {

                    event.preventDefault();

                    alert('Pilih minimal satu data.');

                    return;
                }

                let totalDetails = 0;

                checked.forEach(function(checkbox) {

                    const ids = checkbox.value
                        .split(',')
                        .filter(function(id) {
                            return id.trim() !== '';
                        });

                    totalDetails += ids.length;

                });

                const confirmed = confirm(
                    'Yakin ingin menghapus ' +
                    checked.length +
                    ' product (' +
                    totalDetails +
                    ' data detail)?'
                );

                if (!confirmed) {

                    event.preventDefault();

                    return;
                }

                bulkDeleteInputs.innerHTML = '';

                checked.forEach(function(checkbox) {

                    const ids = checkbox.value.split(',');

                    ids.forEach(function(id) {

                        id = id.trim();

                        if (id === '') {
                            return;
                        }

                        const input =
                            document.createElement('input');

                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;

                        bulkDeleteInputs.appendChild(input);

                    });

                });

            });

            updateBulkButton();

        });
    </script>
@endpush
