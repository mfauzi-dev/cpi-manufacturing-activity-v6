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

        .col-date {
            min-width: 45px;
            font-size: 10px;
        }

        .col-summary {
            min-width: 90px;
            font-weight: bold;
        }

        .col-deduction {
            min-width: 90px;
            color: #c00000;
        }

        .col-net {
            min-width: 90px;
            color: #006100;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #dce6f1;
        }

        .text-muted {
            color: #aaa;
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
            display: inline-block;
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
        <h2>Rekap Payroll Borongan — {{ $periodLabel }}</h2>
        <p>Digenerate pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- SUMMARY --}}
    <div class="summary-section">
        <table>
            <tr>
                <td class="summary-label">Total Karyawan</td>
                <td class="summary-value">{{ $simulations->count() }} orang</td>
                <td width="40"></td>
                <td class="summary-label">Total Gross</td>
                <td class="summary-value">Rp {{ number_format($simulations->sum('total_earning')) }}</td>
                <td width="40"></td>
                <td class="summary-label">Total Potongan</td>
                <td class="summary-value text-danger">
                    Rp
                    {{ number_format($simulations->sum('management_fee') + $simulations->sum('bpjs_kesehatan') + $simulations->sum('bpjs_ketenagakerjaan')) }}
                </td>
                <td width="40"></td>
                <td class="summary-label">Total Gaji Bersih</td>
                <td class="summary-value text-success">Rp {{ number_format($simulations->sum('net_salary')) }}</td>
            </tr>
        </table>
    </div>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-name" style="text-align:left;">Nama Karyawan</th>
                @foreach ($dates as $date)
                    <th class="col-date">{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                @endforeach
                <th class="col-summary">Total Earning</th>
                <th class="col-summary">Total KG</th>
                <th class="col-summary">Hari Kerja</th>
                <th class="col-deduction">Mgmt Fee</th>
                <th class="col-deduction">BPJS Kes.</th>
                <th class="col-deduction">BPJS TK</th>
                <th class="col-net">Gaji Bersih</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($simulations as $i => $sim)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="col-name font-bold" style="text-align:left;">{{ $sim->employee->name }}</td>

                    @foreach ($dates as $date)
                        @php
                            $amount = isset($earningsGrouped[$sim->employee_id][$date])
                                ? $earningsGrouped[$sim->employee_id][$date]->sum('amount')
                                : 0;
                        @endphp
                        <td class="{{ $amount > 0 ? '' : 'text-muted' }}">
                            {{ $amount > 0 ? 'Rp ' . number_format($amount) : '-' }}
                        </td>
                    @endforeach

                    <td class="col-summary">Rp {{ number_format($sim->total_earning) }}</td>
                    <td class="col-summary">{{ number_format($sim->total_kg, 2) }}</td>
                    <td class="col-summary">{{ $sim->work_days }}</td>
                    <td class="text-danger">Rp {{ number_format($sim->management_fee) }}</td>
                    <td class="text-danger">Rp {{ number_format($sim->bpjs_kesehatan) }}</td>
                    <td class="text-danger">Rp {{ number_format($sim->bpjs_ketenagakerjaan) }}</td>
                    <td class="text-success font-bold">Rp {{ number_format($sim->net_salary) }}</td>
                    <td class="{{ $sim->status === 'FINAL' ? 'badge-final' : 'badge-draft' }}">
                        {{ $sim->status }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 11 + count($dates) }}">Tidak ada data payroll untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:left;">Grand Total</td>
                @foreach ($dates as $date)
                    @php
                        $dayTotal = collect($earningsGrouped)->sum(
                            fn($emp) => isset($emp[$date]) ? $emp[$date]->sum('amount') : 0,
                        );
                    @endphp
                    <td class="{{ $dayTotal > 0 ? '' : 'text-muted' }}">
                        {{ $dayTotal > 0 ? 'Rp ' . number_format($dayTotal) : '-' }}
                    </td>
                @endforeach
                <td>Rp {{ number_format($simulations->sum('total_earning')) }}</td>
                <td>{{ number_format($simulations->sum('total_kg'), 2) }}</td>
                <td>{{ $simulations->sum('work_days') }}</td>
                <td class="text-danger">Rp {{ number_format($simulations->sum('management_fee')) }}</td>
                <td class="text-danger">Rp {{ number_format($simulations->sum('bpjs_kesehatan')) }}</td>
                <td class="text-danger">Rp {{ number_format($simulations->sum('bpjs_ketenagakerjaan')) }}</td>
                <td class="text-success">Rp {{ number_format($simulations->sum('net_salary')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
