@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Produktivitas Karyawan</h1>
    </div>

    <div class="section-body">

        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('general-manager.employee-productivity.list') }}">

                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Department</label>

                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">
                                        Semua Department
                                    </option>

                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Cost Center</label>

                                <select name="cost_center_id" id="cost_center_id" class="form-control">
                                    <option value="">
                                        Semua Cost Center
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Search Karyawan</label>

                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="Cari nama atau NIK...">
                            </div>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filter
                        </button>

                        <a href="{{ route('general-manager.employee-productivity.list') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i>
                            Reset
                        </a>
                    </div>

                </form>

            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th>Cost Center</th>
                            <th width="120">Aksi</th>
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
                                    {{ $employee->costCenter->code ?? '-' }}
                                    -
                                    {{ $employee->costCenter->name ?? '-' }}
                                </td>

                                <td class="text-right">

                                    <a href="{{ route('general-manager.employee-productivity.detail', $employee->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Produktivitas
                                    </a>

                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center">
                                    Tidak ada data employee
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

                <div class="card-footer text-right">
                    {{ $employees->withQueryString()->links() }}
                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const selectedCostCenterId = "{{ $costCenterId }}";
            const selectedDepartmentId = "{{ $departmentId }}";

            function loadCostCenters(departmentId, preselect = null) {
                const $costCenterSelect = $('#cost_center_id');

                $costCenterSelect.html('<option value="">Semua Cost Center</option>');

                if (!departmentId) {
                    return;
                }

                $.get(`/employee-productivity/cost-centers/${departmentId}`, function(data) {
                    data.forEach(function(cc) {
                        const selected = (preselect && String(preselect) === String(cc.id)) ?
                            'selected' : '';
                        $costCenterSelect.append(
                            `<option value="${cc.id}" ${selected}>${cc.code} - ${cc.name}</option>`
                        );
                    });
                });
            }

            if (selectedDepartmentId) {
                loadCostCenters(selectedDepartmentId, selectedCostCenterId);
            }

            $('#department_id').on('change', function() {
                loadCostCenters($(this).val());
            });
        });
    </script>
@endpush
