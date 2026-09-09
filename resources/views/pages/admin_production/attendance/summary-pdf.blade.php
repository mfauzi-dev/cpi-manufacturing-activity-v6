<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 20px 20px 25px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        h3 {
            margin: 0 0 4px 0;
            font-size: 15px;
        }

        .subtitle {
            margin: 0 0 10px 0;
            color: #555;
            font-size: 9px;
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
            padding: 2px 10px 2px 0;
            vertical-align: top;
        }

        .filter-info .label {
            font-weight: bold;
            width: 100px;
        }

        table.summary {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.summary th,
        table.summary td {
            border: 1px solid #999;
            padding: 4px 5px;
        }

        table.summary th {
            background-color: #eeeeee;
            text-align: center;
            font-weight: bold;
        }

        table.summary td {
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .nik {
            width: 11%;
        }

        .name {
            width: 18%;
        }

        .department {
            width: 18%;
        }

        .outsourcing {
            width: 12%;
        }

        .group {
            width: 14%;
        }

        .attendance {
            width: 5%;
        }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        .department-row td {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h3>Summary Attendance</h3>

    <p class="subtitle">
        Periode: {{ $month }} {{ $year }}
    </p>

    <div class="filter-info">
        <table>

            <tr>
                <td class="label">
                    Department
                </td>
                <td>
                    : Semua Department
                </td>
            </tr>

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

            <tr>
                <td class="label">
                    Status Karyawan
                </td>
                <td>
                    : {{ $employeeStatusName }}
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
                <th class="nik">NIK</th>
                <th class="name">Nama</th>
                <th class="department">Department</th>
                <th class="outsourcing">OS</th>
                <th class="group">Group</th>
                <th class="attendance">Hadir</th>
                <th class="attendance">Izin</th>
                <th class="attendance">Sakit</th>
                <th class="attendance">Cuti</th>
                <th class="attendance">Alfa</th>
            </tr>
        </thead>

        <tbody>

            @php
                $currentDepartment = null;
            @endphp

            @forelse ($employees as $employee)
                @php
                    $department = $employee->department->name ?? '-';
                @endphp

                @if ($currentDepartment !== $department)
                    @php
                        $currentDepartment = $department;
                    @endphp

                    <tr class="department-row">
                        <td colspan="10">
                            {{ $department }}
                        </td>
                    </tr>
                @endif

                <tr>

                    <td class="text-left">
                        {{ $employee->nik ?? '-' }}
                    </td>

                    <td class="text-left">
                        {{ $employee->name ?? '-' }}
                    </td>

                    <td class="text-left">
                        {{ $department }}
                    </td>

                    <td class="text-left">
                        {{ $employee->outsourcing->name ?? '-' }}
                    </td>

                    <td class="text-left">
                        {{ $employee->psGroup->name ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_hadir ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_izin ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_sakit ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_cuti ?? 0 }}
                    </td>

                    <td class="text-center">
                        {{ $employee->total_alfa ?? 0 }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" style="text-align: center; padding: 10px;">
                        Tidak ada data employee
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>

</body>

</html>
