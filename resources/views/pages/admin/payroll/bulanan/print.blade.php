<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payroll->employee->name }} -
        {{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }} {{ $payroll->period_year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            padding: 32px;
        }

        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .slip-header h2 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .slip-header p {
            font-size: 11px;
            margin: 2px 0;
            color: #444;
        }

        .text-right {
            text-align: right;
        }

        .slip-employee {
            border: 1px solid #bbb;
            padding: 8px 14px;
            margin-bottom: 16px;
            background: #f5f5f5;
        }

        .slip-employee table {
            width: 100%;
        }

        .slip-employee td {
            padding: 3px 10px 3px 0;
            font-size: 12px;
        }

        .slip-body {
            display: flex;
            gap: 14px;
            margin-bottom: 14px;
        }

        .slip-col {
            flex: 1;
            border: 1px solid #bbb;
        }

        .slip-col-title {
            background: #222;
            color: #fff;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .05em;
            padding: 5px 12px;
        }

        .slip-col table {
            width: 100%;
            border-collapse: collapse;
        }

        .slip-col td {
            padding: 5px 12px;
            border-bottom: 1px solid #eee;
        }

        .slip-col td.num {
            text-align: right;
            white-space: nowrap;
        }

        .slip-col tr.total td {
            font-weight: 700;
            border-top: 1px solid #999;
            border-bottom: none;
        }

        .slip-thp {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #222;
            color: #fff;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 28px;
        }

        .slip-footer {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .slip-footer p {
            margin: 2px 0;
        }

        .slip-footer .ttd-space {
            margin: 44px 0 4px;
        }

        .slip-note {
            font-size: 10px;
            color: #888;
            border-top: 1px dashed #bbb;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="slip-header">
        <div>
            <h2>SLIP GAJI KARYAWAN BULANAN</h2>
            <p>Periode: {{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }}
                {{ $payroll->period_year }}</p>
        </div>
        <div class="text-right">
            <p>No: PT Charoen Pokphand
                Indonesia/{{ $payroll->period_year }}/{{ str_pad($payroll->period_month, 2, '0', STR_PAD_LEFT) }}/{{ str_pad($payroll->employee->id, 4, '0', STR_PAD_LEFT) }}
            </p>
            <p>Tanggal Cetak: {{ now()->format('d M Y') }}</p>
            <p>Dibuat oleh: {{ $payroll->generatedBy->name ?? '-' }}</p>
        </div>
    </div>

    {{-- Info Karyawan --}}
    <div class="slip-employee">
        <table>
            <tr>
                <td style="width:100px"><strong>Nama</strong></td>
                <td style="width:200px">: {{ $payroll->employee->name }}</td>
                <td style="width:100px"><strong>Jabatan</strong></td>
                <td>: {{ $payroll->employee->position->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>NIK</strong></td>
                <td>: {{ $payroll->employee->nik }}</td>
                <td><strong>Departemen</strong></td>
                <td>: {{ $payroll->employee->department->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- Rincian 2 Kolom --}}
    <div class="slip-body">

        {{-- Pendapatan --}}
        <div class="slip-col">
            <div class="slip-col-title">PENDAPATAN</div>
            <table>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="num">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Overtime</td>
                    <td class="num">Rp {{ number_format($payroll->overtime_total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Bonus</td>
                    <td class="num">Rp {{ number_format($payroll->bonus_total, 0, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td>Total Pendapatan (Gross)</td>
                    <td class="num">Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Potongan --}}
        <div class="slip-col">
            <div class="slip-col-title">POTONGAN</div>
            <table>
                <tr>
                    <td>Potongan Lainnya</td>
                    <td class="num">Rp {{ number_format($payroll->deduction_total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>BPJS Kesehatan (1%)</td>
                    <td class="num">Rp {{ number_format($payroll->basic_salary * 0.01, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>BPJS Ketenagakerjaan (2%)</td>
                    <td class="num">Rp {{ number_format($payroll->basic_salary * 0.02, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Management Fee (5%)</td>
                    <td class="num">Rp {{ number_format($payroll->basic_salary * 0.05, 0, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td>Total Potongan</td>
                    <td class="num">Rp
                        {{ number_format(
                            $payroll->deduction_total +
                                $payroll->basic_salary * 0.01 +
                                $payroll->basic_salary * 0.02 +
                                $payroll->basic_salary * 0.05,
                            0,
                            ',',
                            '.',
                        ) }}
                    </td>
                </tr>
            </table>
        </div>

    </div>

    {{-- Take Home Pay --}}
    <div class="slip-thp">
        <span>TAKE HOME PAY</span>
        <span>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</span>
    </div>

    {{-- TTD --}}
    <div class="slip-footer">
        <div>
            <p>Mengetahui,</p>
            <p>HRD</p>
            <p class="ttd-space"></p>
            <p>( _________________________ )</p>
        </div>
        <div>
            <p>Penerima,</p>
            <p>{{ $payroll->employee->name }}</p>
            <p class="ttd-space"></p>
            <p>( _________________________ )</p>
        </div>
    </div>

    {{-- Catatan --}}
    <div class="slip-note">
        * Slip gaji ini diterbitkan secara sistem dan berlaku tanpa tanda tangan basah apabila dicetak dari sistem resmi
        perusahaan.
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>

</html>
