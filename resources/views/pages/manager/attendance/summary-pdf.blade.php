<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        h3 {
            margin: 0 0 5px 0;
        }

        .subtitle {
            margin: 0 0 12px 0;
            color: #555;
        }

        .filter-info {
            margin-bottom: 12px;
        }

        .filter-info table {
            width: auto;
            border: none;
        }

        .filter-info td {
            border: none;
            padding: 2px 8px 2px 0;
        }

        .filter-info .label {
            font-weight: bold;
        }

        table.summary {
            width: 100%;
            border-collapse: collapse;
        }

        table.summary th,
        table.summary td {
            border: 1px solid #999;
            padding: 5px;
        }

        table.summary th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .text-center {
            text-align: center;
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
        Summary Attendance
    </h3>

    <p class="subtitle">
        Periode: {{ $month }} {{ $year }}
    </p>

    <div class="filter-info">

        <table>

            <tr>
                <td class="label">
                    Outsourcing
                </td>
                <td>
                    : {{ $outsourcingName }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Cost Center
                </td>
                <td>
                    : {{ $costCenterName }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Group
                </td>
                <td>
                    : {{ $psGroupName }}
                </td>
            </tr>

            @if ($search)
                <tr>
                    <td class="label">
                        Pencarian
                    </td>
                    <td>
                        : {{ $search }}
                    </td>
                </tr>
            @endif

        </table>

    </div>

    <table class="summary">

        <thead>

            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Department</th>
                <th>OS</th>
                <th>Group</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Cuti</th>
                <th>Alfa</th>
            </tr>

        </thead>

        <tbody>

            @forelse ($employees as $employee)
                <tr>

                    <td>
                        {{ $employee->nik }}
                    </td>

                    <td>
                        {{ $employee->name }}
                    </td>

                    <td>
                        {{ $employee->department->name ?? '-' }}
                    </td>

                    <td>
                        {{ $employee->outsourcing->name ?? '-' }}
                    </td>

                    <td>
                        {{ $employee->psGroup->name ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_hadir }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_izin }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_sakit }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_cuti }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_alfa }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" style="text-align: center;">
                        Tidak ada data employee
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>

</body>

</html>
