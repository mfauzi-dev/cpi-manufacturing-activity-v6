@extends('layouts.master')

@section('content')

    <div class="section-header">

        <div>
            <a href="{{ route('general-manager.daily-activity-further.index') }}" class="text-dark mr-2">
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
                    action="{{ route('general-manager.daily-activity-further.detail', [
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

                        <a href="{{ route('general-manager.daily-activity-further.detail', [
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

                <div class="mb-2">

                    <a href="{{ route('general-manager.daily-activity-further.export-excel', [
                        'costCenterId' => $costCenter->id,
                        'psGroupId' => $psGroup->id,
                        'line_id' => $line->id ?? null,
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                    ]) }}"
                        class="btn btn-success">

                        <i class="fas fa-file-excel"></i>
                        Excel

                    </a>

                    <a href="{{ route('general-manager.daily-activity-further.export-pdf', [
                        'costCenterId' => $costCenter->id,
                        'psGroupId' => $psGroup->id,
                        'line_id' => $line->id ?? null,
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                    ]) }}"
                        class="btn btn-danger" target="_blank">

                        <i class="fas fa-file-pdf"></i>
                        PDF

                    </a>

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
                                'detail_id' => $detail->id,
                            ];
                        }

                        $groupedDetails[$groupKey]['products'][$productKey]['total_kg_rm'] +=
                            (float) $detail->total_kg_rm;

                        $groupedDetails[$groupKey]['products'][$productKey]['total_kg_fg'] +=
                            (float) $detail->total_kg_fg;

                        if ($detail->id > $groupedDetails[$groupKey]['products'][$productKey]['detail_id']) {
                            $groupedDetails[$groupKey]['products'][$productKey]['detail_id'] = $detail->id;
                        }
                    }
                @endphp

                <table class="table table-bordered">

                    <thead>

                        <tr>

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

                                $grandTotalFg = 0;

                                foreach ($group['products'] as $item) {
                                    $grandTotalFg += (float) $item['total_kg_fg'];
                                }

                                $productivity = $manHours > 0 ? $grandTotalFg / $manHours : 0;

                                $productCount = count($group['products']);

                                $firstProduct = true;

                            @endphp

                            @foreach ($group['products'] as $product)
                                <tr>

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

                                            {{ number_format($grandTotalFg, 2, ',', '.') }}

                                        </td>

                                        <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                            {{ number_format($manHours, 2, ',', '.') }}

                                        </td>

                                        <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                            {{ number_format($productivity, 2, ',', '.') }}

                                        </td>
                                    @endif

                                </tr>

                                @php
                                    $firstProduct = false;
                                @endphp
                            @endforeach

                        @empty

                            <tr>

                                <td colspan="8" class="text-center">

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
