@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Employee Salary</h1>
    </div>

    <div class="section-body">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('manager.employee-salary.index') }}" method="GET">

                    <div class="row">

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">Semua Department</option>

                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Cost Center</label>
                                <select name="cost_center_id" id="cost_center_id" class="form-control">
                                    <option value="">Semua Cost Center</option>

                                    @foreach ($costCenters as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ request('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->code }} - {{ $costCenter->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Tahun</label>
                                <select name="tahun" class="form-control">
                                    <option value="">Semua Tahun</option>

                                    @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}"
                                            {{ request('tahun') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="NIK / Nama"
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">
                            Terapkan Filter
                        </button>

                        <a href="{{ route('manager.employee-salary.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <div class="card">

            <div class="card-header">
                <div class="w-100 d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">
                        Data Employee Salary
                    </h4>

                    <a href="{{ route('manager.employee-salary.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Salary
                    </a>

                </div>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">
                        <tr>
                            <th width="60">No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Department</th>
                            <th>Tahun</th>
                            <th>Basic Salary</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($employeeSalaries as $employeeSalary)
                            <tr>

                                <td>
                                    {{ $employeeSalaries->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    {{ $employeeSalary->employee->nik ?? '-' }}
                                </td>

                                <td>
                                    {{ $employeeSalary->employee->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employeeSalary->employee->department->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $employeeSalary->tahun }}
                                </td>

                                <td>
                                    Rp {{ number_format($employeeSalary->basic_salary, 0, ',', '.') }}
                                </td>

                                <td>

                                    <a href="{{ route('manager.employee-salary.edit', $employeeSalary->id) }}"
                                        class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('manager.employee-salary.destroy', $employeeSalary->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus salary ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="card-footer text-right">
                {{ $employeeSalaries->withQueryString()->links() }}
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('#department_id').on('change', function() {

                let departmentId = $(this).val();
                let costCenter = $('#cost_center_id');

                costCenter.empty();
                costCenter.append('<option value="">Semua Cost Center</option>');

                if (!departmentId) {
                    return;
                }

                $.ajax({
                    url: "{{ route('employee-salary.cost-centers', ':departmentId') }}"
                        .replace(':departmentId', departmentId),
                    type: "GET",

                    success: function(data) {

                        $.each(data, function(key, value) {

                            costCenter.append(
                                '<option value="' + value.id + '">' +
                                value.code + ' - ' + value.name +
                                '</option>'
                            );

                        });

                    },

                    error: function() {
                        alert('Gagal mengambil data Cost Center.');
                    }
                });

            });

        });
    </script>
@endpush
