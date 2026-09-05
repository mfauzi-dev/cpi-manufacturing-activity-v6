<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Penggajian Borongan</title>

    <style>
        @page {
            /* size: A4 landscape; */
            margin: 20px 15px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header h3 {
            margin: 5px 0 0;
            font-size: 11px;
            font-weight: normal;
        }

        .info {
            width: 100%;
            margin-bottom: 10px;
        }

        .info td {
            padding: 2px 0;
            border: none;
        }

        .data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data th,
        .data td {
            border: 1px solid #000;
            padding: 4px 3px;
        }

        .data th {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }

        .data td {
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .total {
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

    <div class="header">
        <h2>PENGGAJIAN BORONGAN</h2>
        <h3>Periode {{ $periodLabel }}</h3>
    </div>

    <table class="info">
        <tr>
            <td width="15%">
                <strong>Department</strong>
            </td>
            <td>
                : {{ $departmentName }}
            </td>
        </tr>

        @isset($outsourcingName)
            <tr>
                <td width="15%">
                    <strong>Outsourcing</strong>
                </td>
                <td>
                    : {{ $outsourcingName }}
                </td>
            </tr>
        @endisset

        @isset($costCenterName)
            <tr>
                <td width="15%">
                    <strong>Cost Center</strong>
                </td>
                <td>
                    : {{ $costCenterName }}
                </td>
            </tr>
        @endisset
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">
                    NO
                </th>

                <th style="width: 9%;">
                    NO. KTP
                </th>

                <th style="width: 8%;">
                    NIK AML
                </th>

                <th style="width: 15%;">
                    NAMA
                </th>

                <th style="width: 8%;">
                    HASIL PROSES
                    <br>
                    (Kg)
                </th>

                <th style="width: 6%;">
                    TOTAL HARI
                </th>

                <th style="width: 10%;">
                    TOTAL UPAH
                    <br>
                    YANG DITERIMA
                </th>

                <th style="width: 9%;">
                    JAMSOSTEK
                    <br>
                    (4.89%)
                </th>

                <th style="width: 9%;">
                    BPJS KESEHATAN
                    <br>
                    (4%)
                </th>

                <th style="width: 9%;">
                    BPJS PENSIUN
                    <br>
                    (2%)
                </th>

                <th style="width: 10%;">
                    MANAGEMEN FEE
                    <br>
                    (175000/25)
                </th>

                <th style="width: 11%;">
                    GRAND TOTAL
                    <br>
                    UPAH DITERIMA
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($payrolls as $i => $payroll)
                <tr>
                    <td class="text-center">
                        {{ $i + 1 }}
                    </td>

                    <td>
                        {{ $payroll->employee->ktp_number ?? '-' }}
                    </td>

                    <td>
                        {{ $payroll->employee->nik ?? '-' }}
                    </td>

                    <td>
                        {{ $payroll->employee->name ?? '-' }}
                    </td>

                    <td class="text-right">
                        {{ number_format($payroll->total_kg ?? 0, 2, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ $payroll->total_hari_kerja ?? 0 }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->total_upah ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->jamsostek ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->bpjs_kesehatan ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->bpjs_pensiun ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->managemen_fee ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right bold">
                        Rp {{ number_format($payroll->grand_total_upah ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">
                        Belum ada penggajian borongan untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if ($payrolls->count() > 0)
            <tfoot>
                <tr class="total">
                    <td colspan="4" class="text-right">
                        TOTAL
                    </td>

                    <td class="text-right">
                        {{ number_format($grandTotalKg ?? 0, 2, ',', '.') }}
                    </td>

                    <td></td>

                    <td class="text-right">
                        Rp {{ number_format($grandTotalUpah ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payrolls->sum('jamsostek'), 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payrolls->sum('bpjs_kesehatan'), 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payrolls->sum('bpjs_pensiun'), 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payrolls->sum('managemen_fee'), 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payrolls->sum('grand_total_upah'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>

</html>
