@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Import Karyawan</h1>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('admin.employee.upload') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Jenis Karyawan</label>

                        <select name="employment_status" id="employment_status"
                            class="form-control @error('employment_status') is-invalid @enderror">

                            <option value="">Pilih Jenis Karyawan</option>

                            <option value="permanent" {{ old('employment_status') == 'permanent' ? 'selected' : '' }}>
                                Permanent
                            </option>

                            <option value="outsourcing" {{ old('employment_status') == 'outsourcing' ? 'selected' : '' }}>
                                Outsourcing
                            </option>

                        </select>

                        @error('employment_status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group d-none" id="outsourcing-wrapper">
                        <label>Outsourcing</label>

                        <select name="outsourcing_id" class="form-control @error('outsourcing_id') is-invalid @enderror">

                            <option value="">Pilih Outsourcing</option>

                            @foreach ($outsourcings as $outsourcing)
                                <option value="{{ $outsourcing->id }}"
                                    {{ old('outsourcing_id') == $outsourcing->id ? 'selected' : '' }}>
                                    {{ $outsourcing->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('outsourcing_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group d-none" id="employee-status-wrapper">
                        <label>Status Karyawan Outsourcing</label>

                        <select name="employee_status" class="form-control @error('employee_status') is-invalid @enderror">

                            <option value="">Pilih Status</option>

                            <option value="borongan" {{ old('employee_status') == 'borongan' ? 'selected' : '' }}>
                                Borongan
                            </option>

                            <option value="harian" {{ old('employee_status') == 'harian' ? 'selected' : '' }}>
                                Harian
                            </option>

                        </select>

                        @error('employee_status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>File Excel</label>

                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                            accept=".xlsx,.xls">

                        @error('file')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            Upload & Import
                        </button>

                        <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function toggleOutsourcing() {

            const isOutsourcing = $('#employment_status').val() === 'outsourcing';

            if (isOutsourcing) {
                $('#outsourcing-wrapper').removeClass('d-none');
                $('#employee-status-wrapper').removeClass('d-none');
            } else {
                $('#outsourcing-wrapper').addClass('d-none');
                $('#employee-status-wrapper').addClass('d-none');

                $('select[name="outsourcing_id"]').val('');
                $('select[name="employee_status"]').val('');
            }
        }

        $('#employment_status').on('change', toggleOutsourcing);

        toggleOutsourcing();
    </script>
@endpush
