<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8">

    <title>Penggajian Borongan</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 15px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .header h3 {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
        }

        td.center {
            text-align: center;
        }

        td.right {
            text-align: right;
        }

        .grand-total {
            font-weight: bold;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>

</head>

<body>

    {{-- HEADER PDF --}}
    <div class="header">

        <h2>PENGGAJIAN BORONGAN</h2>

        <h3>
            Periode: {{ $periodLabel }}
        </h3>

    </div>


    {{-- TABLE --}}
    <table>

        <thead>

            <tr>

                <th width="4%">
                    No.
                </th>

                <th width="9%">
                    No. KTP
                </th>

                <th width="8%">
                    NIK
                </th>

                <th width="15%">
                    Nama
                </th>

                <th width="8%">
                    Hasil Proses
                    <br>
                    (Kg)/Jam
                </th>

                <th width="6%">
                    Total Hari
                </th>

                <th width="10%">
                    Total Upah
                </th>

                <th width="9%">
                    Jamsostek
                    <br>
                    (4.89%)
                </th>

                <th width="9%">
                    BPJS Kesehatan
                    <br>
                    (4%)
                </th>

                <th width="9%">
                    BPJS Pensiun
                    <br>
                    (2%)
                </th>

                <th width="10%">
                    Managemen Fee
                    <br>
                    (6800 × Hari Kerja)
                </th>

                <th width="11%">
                    Grand Total
                    <br>
                    Upah Diterima
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse ($payrolls as $i => $payroll)
                <tr>

                    {{-- NO --}}
                    <td class="center">
                        {{ $i + 1 }}
                    </td>


                    {{-- NO KTP --}}
                    <td>
                        {{ $payroll->employee->ktp_number ?? '-' }}
                    </td>


                    {{-- NIK --}}
                    <td>
                        {{ $payroll->employee->nik ?? '-' }}
                    </td>


                    {{-- NAMA --}}
                    <td>
                        {{ $payroll->employee->name ?? '-' }}
                    </td>


                    {{-- PRODUKTIVITAS --}}
                    <td class="center">
                        {{ number_format($payroll->total_kg ?? 0, 2, ',', '.') }}
                    </td>


                    {{-- TOTAL HARI --}}
                    <td class="center">
                        {{ $payroll->total_hari_kerja ?? 0 }}
                    </td>


                    {{-- TOTAL UPAH --}}
                    <td class="right">
                        Rp {{ number_format($payroll->total_upah ?? 0, 0, ',', '.') }}
                    </td>


                    {{-- JAMSOSTEK --}}
                    <td class="right">
                        Rp {{ number_format($payroll->jamsostek ?? 0, 0, ',', '.') }}
                    </td>


                    {{-- BPJS KESEHATAN --}}
                    <td class="right">
                        Rp {{ number_format($payroll->bpjs_kesehatan ?? 0, 0, ',', '.') }}
                    </td>


                    {{-- BPJS PENSIUN --}}
                    <td class="right">
                        Rp {{ number_format($payroll->bpjs_pensiun ?? 0, 0, ',', '.') }}
                    </td>


                    {{-- MANAGEMENT FEE --}}
                    <td class="right">
                        Rp {{ number_format($payroll->managemen_fee ?? 0, 0, ',', '.') }}
                    </td>


                    {{-- GRAND TOTAL --}}
                    <td class="right grand-total">
                        Rp {{ number_format($payroll->grand_total_upah ?? 0, 0, ',', '.') }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="12" style="text-align: center;">
                        Belum ada penggajian borongan untuk periode ini.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </table>

</body>

</html>
