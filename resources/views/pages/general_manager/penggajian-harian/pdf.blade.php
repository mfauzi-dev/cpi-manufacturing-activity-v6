<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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
        <strong>UMP:</strong> Rp {{ number_format($ump, 0, ',', '.') }}
        /
        {{ $hariKerjaStandar }} hari
    </div>

    <table>
        <thead>
            <tr>
                <th width="35">No</th>
                <th>Nama</th>
                <th>Department</th>
                <th>Hari Kerja</th>
                <th>Upah Harian</th>
                <th>Gaji Bersih</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($payrolls as $payroll)
                <tr>
                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $payroll->employee->name ?? '-' }}
                    </td>

                    <td>
                        {{ $payroll->employee->department->name ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $payroll->work_days }} hari
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->upah_harian, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Belum ada payroll upah harian untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr class="total">
                <td colspan="3">GRAND TOTAL</td>

                <td class="text-center">
                    {{ number_format($grandTotalWorkDays, 0, ',', '.') }} hari
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalUpahHarian, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($grandTotalNetSalary, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
