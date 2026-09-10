<!DOCTYPE html>

<html>

<head>


    <meta charset="utf-8">

    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        h3 {
            margin-bottom: 2px;
        }

        .subtitle {
            margin-top: 0;
            margin-bottom: 15px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 5px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .align-middle {
            vertical-align: middle;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }
    </style>

</head>

<body>

    <h3>{{ $psGroupName }} - {{ $costCenterName }}</h3>

    <p class="subtitle">
        Periode: {{ $fromDate }} s/d {{ $toDate }}
    </p>

    @php

        /*
         * GROUP BERDASARKAN:
         * Tanggal + Line
         */
        $groupedData = [];

        foreach ($data as $row) {
            $daf = $row->dailyActivityFurther;

            $groupKey = $daf->tanggal->format('Y-m-d') . '_' . $daf->line_id;

            if (!isset($groupedData[$groupKey])) {
                $groupedData[$groupKey] = [
                    'tanggal' => $daf->tanggal,
                    'line_name' => $daf->line->name ?? '-',
                    'employees' => [],
                    'products' => [],
                    'input_by' => $daf->inputBy->name ?? '-',
                ];
            }

            /*
             * Ambil employee unik
             */
            foreach ($daf->employees as $employee) {
                if (!isset($groupedData[$groupKey]['employees'][$employee->id])) {
                    $groupedData[$groupKey]['employees'][$employee->id] = [
                        'name' => $employee->name,
                        'jumlah_hk' => (float) $employee->pivot->jumlah_hk,
                    ];
                }
            }

            /*
             * Group product
             */
            $productKey = $row->product_id;

            if (!isset($groupedData[$groupKey]['products'][$productKey])) {
                $groupedData[$groupKey]['products'][$productKey] = [
                    'material_code' => $row->product->material_code ?? '-',
                    'material_name' => $row->product->material_name ?? '-',
                    'total_kg_rm' => 0,
                    'total_kg_fg' => 0,
                ];
            }

            $groupedData[$groupKey]['products'][$productKey]['total_kg_rm'] += (float) $row->total_kg_rm;

            $groupedData[$groupKey]['products'][$productKey]['total_kg_fg'] += (float) $row->total_kg_fg;
        }
    @endphp

    <table>

        <thead>

            <tr>

                <th rowspan="2">
                    Tanggal
                </th>

                <th rowspan="2">
                    Line
                </th>

                <th rowspan="2">
                    Kode Material
                </th>

                <th rowspan="2">
                    Nama Material
                </th>

                <th colspan="2">
                    Production
                </th>

                <th rowspan="2">
                    Total FG
                </th>

                <th rowspan="2">
                    Man Hours
                </th>

                <th rowspan="2">
                    Productivity
                </th>

                <th rowspan="2">
                    Nama Karyawan
                </th>

                <th rowspan="2">
                    Diinput Oleh
                </th>

            </tr>

            <tr>

                <th>
                    RM
                </th>

                <th>
                    FG
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($groupedData as $group)

                @php

                    $manHours = 0;

                    foreach ($group['employees'] as $employee) {
                        $manHours += (float) $employee['jumlah_hk'];
                    }

                    $totalFg = 0;

                    foreach ($group['products'] as $product) {
                        $totalFg += (float) $product['total_kg_fg'];
                    }

                    /*
                     * PRODUCTIVITY:
                     *
                     * Total FG / Man Hours
                     */
                    $productivity = $manHours > 0 ? $totalFg / $manHours : 0;

                    $productCount = count($group['products']);

                    $employeeNames = [];

                    foreach ($group['employees'] as $employee) {
                        $employeeNames[] = $employee['name'];
                    }

                    $employeeNames = implode(', ', $employeeNames);

                    $firstProduct = true;

                @endphp

                @foreach ($group['products'] as $product)
                    <tr>

                        @if ($firstProduct)
                            <td rowspan="{{ $productCount }}" class="align-middle">

                                {{ $group['tanggal']->format('d M Y') }}

                            </td>

                            <td rowspan="{{ $productCount }}" class="align-middle">

                                {{ $group['line_name'] }}

                            </td>
                        @endif

                        <td>

                            {{ $product['material_code'] }}

                        </td>

                        <td>

                            {{ $product['material_name'] }}

                        </td>

                        <td class="text-right">

                            {{ number_format($product['total_kg_rm'], 2, ',', '.') }}

                        </td>

                        <td class="text-right">

                            {{ number_format($product['total_kg_fg'], 2, ',', '.') }}

                        </td>

                        @if ($firstProduct)
                            <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                {{ number_format($totalFg, 2, ',', '.') }}

                            </td>

                            <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                {{ number_format($manHours, 2, ',', '.') }}

                            </td>

                            <td rowspan="{{ $productCount }}" class="text-right align-middle">

                                {{ number_format($productivity, 2, ',', '.') }}

                            </td>

                            <td rowspan="{{ $productCount }}" class="align-middle">

                                {{ $employeeNames ?: '-' }}

                            </td>

                            <td rowspan="{{ $productCount }}" class="align-middle">

                                {{ $group['input_by'] }}

                            </td>
                        @endif

                    </tr>

                    @php
                        $firstProduct = false;
                    @endphp
                @endforeach

            @empty

                <tr>

                    <td colspan="11" class="text-center">

                        Tidak ada data

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


</body>

</html>
