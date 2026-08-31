<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .info {
            margin-bottom: 15px;
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
            background-color: #eee;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background-color: #eee;
        }
    </style>

</head>

<body>

    <h2>PENGGAJIAN HARIAN</h2>

    <div class="info">
        <strong>Periode:</strong> {{ $periodLabel }}<br>

        <strong>Department:</strong> {{ $departmentName }}<br>

        <strong>UMP:</strong>
        Rp {{ number_format($ump ?? 0, 0, ',', '.') }}
        /
        {{ $hariKerjaStandar ?? 25 }} hari
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>No. KTP</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Hari Kerja</th>
                <th>Upah Harian</th>
                <th>Jamsostek<br>(4,89%)</th>
                <th>BPJS Kesehatan<br>(4%)</th>
                <th>BPJS Pensiun<br>(2%)</th>
                <th>Management Fee<br>(6.800 × Hari Kerja)</th>
                <th>Gaji Bersih</th>
                <th>Grand Total Upah</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($payrolls as $payroll)
                <tr>

                    {{-- NO --}}
                    <td class="text-center">
                        {{ $loop->iteration }}
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

                    {{-- HARI KERJA --}}
                    <td class="text-center">
                        {{ $payroll->work_days ?? 0 }}
                    </td>

                    {{-- UPAH HARIAN --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->upah_harian ?? 0, 0, ',', '.') }}
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

                    {{-- GAJI BERSIH --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->net_salary ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- GRAND TOTAL --}}
                    <td class="text-right">
                        Rp {{ number_format($payroll->grand_total_upah ?? 0, 0, ',', '.') }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="12" class="text-center">
                        Belum ada penggajian upah harian untuk periode ini.
                    </td>
                </tr>
            @endforelse

        </tbody>

        <tfoot>

            <tr class="total">

                <td colspan="4">
                    GRAND TOTAL
                </td>

                <td class="text-center">
                    {{ number_format($grandTotalWorkDays ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalUpahHarian ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalJamsostek ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalBpjsKesehatan ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalBpjsPensiun ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalManagementFee ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalNetSalary ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalUpah ?? 0, 0, ',', '.') }}
                </td>

            </tr>

        </tfoot>

    </table>

</body>

</html>
