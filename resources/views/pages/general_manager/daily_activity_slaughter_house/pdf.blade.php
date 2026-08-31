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

    <h3>
        {{ $psGroupName }} - {{ $costCenterName }}
        @if (!empty($lineName))
            - {{ $lineName }}
        @endif
    </h3>

    <p class="subtitle">
        Periode: {{ $fromDate }} s/d {{ $toDate }}
    </p>

    <table>

        <thead>

            <tr>

                <th>Tanggal</th>

                <th>Line</th>

                <th>Product Group</th>

                <th>Kode Material</th>

                <th>Nama Material</th>

                <th>Nama Karyawan</th>

                <th class="text-right">
                    Kg
                </th>

                <th class="text-right">
                    Harga / Kg
                </th>

                <th class="text-right">
                    Total Harga
                </th>

                <th>
                    Diinput Oleh
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($data as $row)
                @php
                    $dah = $row->dailyActivitySlaughterHouse;
                @endphp

                <tr>

                    <td>
                        {{ $dah->tanggal->format('d M Y') }}
                    </td>

                    <td>
                        {{ $dah->line->name ?? '-' }}
                    </td>

                    <td>
                        {{ $dah->productGroup->name ?? '-' }}
                    </td>

                    <td>
                        {{ $row->product->material_code ?? '-' }}
                    </td>

                    <td>
                        {{ $row->product->material_name ?? '-' }}
                    </td>

                    <td>
                        {{ $dah->employee->name ?? '-' }}
                    </td>

                    <td class="text-right">
                        {{ number_format($row->total_kg, 2, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($row->harga_per_kg, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($row->total_harga, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ $dah->inputBy->name ?? '-' }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="10" style="text-align:center;">
                        Tidak ada data
                    </td>

                </tr>
            @endforelse

        </tbody>

    </table>
</body>

</html>
