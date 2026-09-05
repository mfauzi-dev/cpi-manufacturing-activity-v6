<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        h3 {
            margin-bottom: 2px;
        }

        .subtitle {
            margin-top: 0;
            margin-bottom: 5px;
            color: #555;
        }

        .info {
            margin-top: 0;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
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

    @php
        $employee = $data->first()->employee ?? null;
        $departmentName = strtolower(trim($employee->department->name ?? ''));
    @endphp

    <h3>
        {{ $employee->name ?? '-' }}
    </h3>

    <p class="subtitle">
        NIK: {{ $employee->nik ?? '-' }}
    </p>

    <p class="subtitle">
        Department: {{ $employee->department->name ?? '-' }}
    </p>

    <p class="info">
        Cost Center:
        {{ $employee->costCenter->code ?? '-' }}
        -
        {{ $employee->costCenter->name ?? '-' }}

        <br>

        Periode:
        {{ $fromDate }}
        s/d
        {{ $toDate }}
    </p>

    <table>

        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Produk</th>

                <th class="text-right">
                    Productivity
                </th>

                <th class="text-right">
                    Total KG
                </th>

                @if ($departmentName !== 'further processing')
                    <th class="text-right">
                        Total Rupiah
                    </th>
                @endif
            </tr>
        </thead>

        <tbody>

            @forelse ($data as $item)

                <tr>

                    <td>
                        {{ $item->activity_date ? \Carbon\Carbon::parse($item->activity_date)->format('d M Y') : '-' }}
                    </td>

                    <td>
                        {{ $item->product->material_name ?? '-' }}
                    </td>

                    <td class="text-right">

                        @if ($departmentName === 'slaughter house')
                            {{ $item->display_productivity_actual !== null
                                ? number_format($item->display_productivity_actual, 2, ',', '.')
                                : '-' }}
                        @else
                            {{ $item->display_productivity !== null ? number_format($item->display_productivity, 2, ',', '.') : '-' }}
                        @endif

                    </td>

                    <td class="text-right">
                        {{ number_format($item->total_kg ?? 0, 2, ',', '.') }}
                    </td>

                    @if ($departmentName !== 'further processing')
                        <td class="text-right">

                            @if ($item->display_total_harga !== null)
                                Rp {{ number_format($item->display_total_harga, 0, ',', '.') }}
                            @else
                                -
                            @endif

                        </td>
                    @endif

                </tr>

            @empty

                <tr>
                    <td colspan="{{ $departmentName === 'further processing' ? 4 : 5 }}" style="text-align: center;">
                        Tidak ada data
                    </td>
                </tr>

            @endforelse

        </tbody>

        <tfoot>

            <tr>

                <th colspan="3" style="text-align: right;">
                    TOTAL
                </th>

                <th class="text-right">
                    {{ number_format($data->sum('total_kg'), 2, ',', '.') }}
                </th>

                @if ($departmentName !== 'further processing')
                    <th class="text-right">
                        Rp
                        {{ number_format($data->sum('display_total_harga'), 0, ',', '.') }}
                    </th>
                @endif

            </tr>

        </tfoot>

    </table>

</body>

</html>
