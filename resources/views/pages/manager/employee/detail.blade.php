@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Detail Karyawan</h1>
    </div>

    <div class="section-body">
        <div class="row">

            {{-- PROFILE --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">

                        <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=6777ef&color=fff&size=150"
                            class="rounded-circle mb-3">

                        <h4 class="mb-1">
                            {{ $employee->name }}
                        </h4>

                        <p class="text-muted mb-2">
                            {{ $employee->nik ?? '-' }}
                        </p>

                        {{-- STATUS AKTIF --}}
                        @if ($employee->is_active)
                            <span class="badge badge-success">
                                Aktif
                            </span>
                        @else
                            <span class="badge badge-danger">
                                Tidak Aktif
                            </span>
                        @endif

                        {{-- JENIS KARYAWAN --}}
                        <br><br>

                        @if ($employee->employment_status == 'permanent')
                            <span class="badge badge-primary">
                                Permanent
                            </span>
                        @else
                            <span class="badge badge-warning">
                                Outsourcing
                            </span>
                        @endif

                        {{-- STATUS KARYAWAN --}}
                        @if ($employee->employee_status)
                            <br><br>
                            <span class="badge badge-info">
                                {{ ucfirst($employee->employee_status) }}
                            </span>
                        @endif

                    </div>
                </div>
            </div>

            {{-- DATA --}}
            <div class="col-lg-8">

                {{-- INFORMASI PEKERJAAN --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Pekerjaan</h4>
                    </div>

                    <div class="card-body">
                        <table class="table table-borderless">

                            <tr>
                                <th width="220">Department</th>
                                <td>
                                    {{ $employee->department->name ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Cost Center</th>
                                <td>
                                    {{ $employee->costCenter->name ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>PS Group</th>
                                <td>
                                    {{ $employee->psGroup->name ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Position</th>
                                <td>
                                    {{ $employee->position->name ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Outsourcing</th>
                                <td>
                                    {{ $employee->outsourcing->name ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Personel Area</th>
                                <td>
                                    {{ $employee->personel_area ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Join Date</th>
                                <td>
                                    {{ optional($employee->join_date)->format('d M Y') ?? '-' }}
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>

                {{-- INFORMASI PRIBADI --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Pribadi</h4>
                    </div>

                    <div class="card-body">
                        <table class="table table-borderless">

                            <tr>
                                <th width="220">No. KTP</th>
                                <td>
                                    {{ $employee->ktp_number ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Gender</th>
                                <td>
                                    {{ $employee->gender ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Birthplace</th>
                                <td>
                                    {{ $employee->birthplace ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Religion</th>
                                <td>
                                    {{ $employee->religious ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Age</th>
                                <td>
                                    {{ $employee->age ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Pers No</th>
                                <td>
                                    {{ $employee->pers_no ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Personal Number</th>
                                <td>
                                    {{ $employee->personal_number ?? '-' }}
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>

                {{-- BUTTON --}}
                <div class="text-right">
                    <a href="{{ route('manager.employee.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
