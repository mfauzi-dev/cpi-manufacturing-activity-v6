@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Employee Daily Earnings</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.daily-earning.index') }}">Daily Earnings</a>
            </div>
            <div class="breadcrumb-item">Tambah</div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('errors'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('errors') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Tambah Daily Earning</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.overtime.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <div class="form-group">
                        <label>Karyawan</label>

                        <select name="employee_id"
                            class="form-control select2-ajax @error('employee_id') is-invalid @enderror"></select>

                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Work Date</label>
                        <input type="date" name="work_date" value="{{ old('work_date') }}"
                            class="form-control @error('work_date') is-invalid @enderror">

                        @error('work_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Hours</label>
                        <input type="number" name="hours" value="{{ old('hours') }}"
                            class="form-control @error('hours') is-invalid @enderror" min="1">

                        @error('hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Rate per Hour</label>
                        <input type="number" name="rate_per_hour" value="{{ old('rate_per_hour') }}"
                            class="form-control @error('rate_per_hour') is-invalid @enderror" min="0">

                        @error('rate_per_hour')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>

                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('.select2-ajax').select2({
                placeholder: 'Search employee...',
                width: '100%',
                ajax: {
                    url: '/employees/search',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.id,
                                text: item.name + ' - ' + item.nik
                            }))
                        };
                    },
                    cache: true
                }
            });

        });
    </script>
@endpush
