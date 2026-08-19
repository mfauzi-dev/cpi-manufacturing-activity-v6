@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Import Karyawan</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('personalia.employee.upload') }}" enctype="multipart/form-data">

                    @csrf

                    <div class="form-group">
                        <label>Jenis Karyawan</label>

                        <select name="employment_status" id="employment_status" class="form-control" required>
                            <option value="">
                                Pilih Jenis Karyawan
                            </option>

                            <option value="permanent">
                                Permanent
                            </option>

                            <option value="outsourcing">
                                Outsourcing
                            </option>
                        </select>
                    </div>

                    <div class="form-group d-none" id="outsourcing-wrapper">

                        <label>Outsourcing</label>

                        <select name="outsourcing_id" class="form-control">

                            <option value="">
                                Pilih Outsourcing
                            </option>

                            @foreach ($outsourcings as $outsourcing)
                                <option value="{{ $outsourcing->id }}">
                                    {{ $outsourcing->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="form-group">
                        <label>File Excel</label>

                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            Upload & Import
                        </button>

                        <a href="{{ route('personalia.employee.index') }}" class="btn btn-secondary">
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
        $('#employment_status').on('change', function() {

            if ($(this).val() === 'outsourcing') {
                $('#outsourcing-wrapper').removeClass('d-none');
            } else {
                $('#outsourcing-wrapper').addClass('d-none');
            }

        });
    </script>
@endpush
