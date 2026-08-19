<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .title-section {
            margin-bottom: 10px;
        }

        .title-section h2 {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .title-section p {
            font-size: 11px;
            margin: 2px 0;
            color: #555;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 4px 6px;
            text-align: center;
            white-space: nowrap;
        }

        thead tr th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
        }

        .col-name {
            text-align: left;
            min-width: 140px;
        }

        .col-no {
            width: 30px;
        }

        .col-summary {
            min-width: 100px;
            font-weight: bold;
        }

        .col-deduction {
            min-width: 100px;
            color: #c00000;
        }

        .col-net {
            min-width: 100px;
            color: #006100;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .text-danger {
            color: #c00000;
        }

        .text-success {
            color: #006100;
        }

        .font-bold {
            font-weight: bold;
        }

        tfoot tr td {
            background-color: #dce6f1;
            font-weight: bold;
            font-size: 11px;
        }

        .badge-final {
            color: #006100;
            font-weight: bold;
        }

        .badge-draft {
            color: #7d6608;
            font-weight: bold;
        }

        .summary-section {
            margin-bottom: 12px;
            border: 1px solid #bbb;
            padding: 8px 12px;
            background-color: #f9f9f9;
            width: 100%;
        }

        .summary-section table {
            border: none;
            width: auto;
        }

        .summary-section td,
        .summary-section th {
            border: none;
            padding: 2px 16px 2px 0;
            text-align: left;
            font-size: 11px;
            white-space: nowrap;
        }

        .summary-label {
            color: #555;
        }

        .summary-value {
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- TITLE --}}
    <div class="title-section">
        <h2>Rekap Payroll Bulanan — {{ $periodLabel }}</h2>
        <p>Digenerate pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- SUMMARY --}}
    <div class="summary-section">
        <table>
            <tr>
                <td class="summary-label">Total Karyawan</td>
                <td class="summary-value">{{ $payrollBulanan->count() }} orang</td>
                <td width="40"></td>
                <td class="summary-label">Total Gross</td>
                <td class="summary-value">Rp {{ number_format($payrollBulanan->sum('gross_salary')) }}</td>
                <td width="40"></td>
                <td class="summary-label">Total Potongan</td>
                <td class="summary-value text-danger">Rp {{ number_format($payrollBulanan->sum('deduction_total')) }}
                </td>
                <td width="40"></td>
                <td class="summary-label">Total Gaji Bersih</td>
                <td class="summary-value text-success">Rp {{ number_format($payrollBulanan->sum('net_salary')) }}</td>
            </tr>
        </table>
    </div>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-name" style="text-align:left;">Nama Karyawan</th>
                <th class="col-summary">Gaji Pokok</th>
                <th class="col-summary">Overtime</th>
                <th class="col-summary">Bonus</th>
                <th class="col-summary">Gross Salary</th>
                <th class="col-deduction">Potongan</th>
                <th class="col-deduction">BPJS Kes.</th>
                <th class="col-deduction">BPJS TK</th>
                <th class="col-deduction">Mgmt Fee</th>
                <th class="col-net">Gaji Bersih</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payrollBulanan as $i => $payroll)
                @php
                    $bpjsKes = $payroll->basic_salary * 0.01;
                    $bpjsTk = $payroll->basic_salary * 0.02;
                    $mgmtFee = $payroll->basic_salary * 0.05;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="col-name font-bold" style="text-align:left;">{{ $payroll->employee->name }}</td>
                    <td class="col-summary">Rp {{ number_format($payroll->basic_salary) }}</td>
                    <td class="col-summary">Rp {{ number_format($payroll->overtime_total) }}</td>
                    <td class="col-summary">Rp {{ number_format($payroll->bonus_total) }}</td>
                    <td class="col-summary">Rp {{ number_format($payroll->gross_salary) }}</td>
                    <td class="text-danger">Rp {{ number_format($payroll->deduction_total) }}</td>
                    <td class="text-danger">Rp {{ number_format($bpjsKes) }}</td>
                    <td class="text-danger">Rp {{ number_format($bpjsTk) }}</td>
                    <td class="text-danger">Rp {{ number_format($mgmtFee) }}</td>
                    <td class="text-success font-bold">Rp {{ number_format($payroll->net_salary) }}</td>
                    <td class="{{ $payroll->status === 'FINAL' ? 'badge-final' : 'badge-draft' }}">
                        {{ $payroll->status }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">Tidak ada data payroll untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:left;">Grand Total</td>
                <td>Rp {{ number_format($payrollBulanan->sum('basic_salary')) }}</td>
                <td>Rp {{ number_format($payrollBulanan->sum('overtime_total')) }}</td>
                <td>Rp {{ number_format($payrollBulanan->sum('bonus_total')) }}</td>
                <td>Rp {{ number_format($payrollBulanan->sum('gross_salary')) }}</td>
                <td class="text-danger">Rp {{ number_format($payrollBulanan->sum('deduction_total')) }}</td>
                <td class="text-danger">Rp {{ number_format($payrollBulanan->sum('basic_salary') * 0.01) }}</td>
                <td class="text-danger">Rp {{ number_format($payrollBulanan->sum('basic_salary') * 0.02) }}</td>
                <td class="text-danger">Rp {{ number_format($payrollBulanan->sum('basic_salary') * 0.05) }}</td>
                <td class="text-success">Rp {{ number_format($payrollBulanan->sum('net_salary')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
