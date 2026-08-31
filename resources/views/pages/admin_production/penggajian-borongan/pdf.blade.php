<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Penggajian Borongan</title>

    <style>
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
            margin: 5px 0 0;
            font-size: 12px;
            font-weight: normal;
        }

        .info {
            margin-bottom: 10px;
        }

        .info table {
            width: 100%;
            border: none;
        }

        .info td {
            border: none;
            padding: 2px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 5px 4px;
        }

        table.data th {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }

        table.data td {
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
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>PENGGAJIAN BORONGAN</h2>
        <h3>Periode {{ $periodLabel }}</h3>
    </div>

    {{-- INFO --}}
    <div class="info">
        <table>
            <tr>
                <td width="15%"><strong>Department</strong></td>
                <td>: {{ $departmentName }}</td>
            </tr>
            @isset($outsourcingName)
                <tr>
                    <td width="15%"><strong>Outsourcing</strong></td>
                    <td>: {{ $outsourcingName }}</td>
                </tr>
            @endisset
        </table>
    </div>

    {{-- TABLE --}}
    <table class="data">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th>NO. KTP</th>
                <th>NIK AML</th>
                <th>NAMA</th>
                <th>HASIL PROSES (Kg)</th>
                <th>TOTAL HARI</th>
                <th>TOTAL UPAH YANG DITERIMA</th>
                <th>JAMSOSTEK (4.89%)</th>
                <th>BPJS KESEHATAN (4%)</th>
                <th>BPJS PENSIUN (2%)</th>
                <th>MANAGEMEN FEE<br>(6800 × per Hari Kerja)</th>
                <th>GRAND TOTAL UPAH DITERIMA</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($payrolls as $i => $payroll)
                <tr>
                    {{-- NO --}}
                    <td class="text-center">
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

                    {{-- HASIL PROSES --}}
                    <td class="text-right">
                        {{ number_format($payroll->total_kg ?? 0, 2, ',', '.') }}
                    </td>

                    {{-- TOTAL HARI --}}
                    <td class="text-center">
                        {{ $payroll->total_hari_kerja ?? 0 }}
                    </td>

                    {{-- TOTAL UPAH --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->total_upah ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- JAMSOSTEK --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->jamsostek ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- BPJS KESEHATAN --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->bpjs_kesehatan ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- BPJS PENSIUN --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->bpjs_pensiun ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- MANAGEMENT FEE --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->managemen_fee ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- GRAND TOTAL --}}
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

        {{-- TOTAL --}}
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
