@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Import Daily Activity</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show">
                {{ session('warning') }}
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if (session('import_errors') && count(session('import_errors')))
            <div class="alert alert-warning">
                <strong>Baris berikut dilewati saat import:</strong>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin-production.daily-activity.upload') }}" method="POST" enctype="multipart/form-data"
            id="dailyActivityImportForm">

            @csrf

            <div class="card">

                <div class="card-body">

                    <div class="row">

                        {{-- Tanggal --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal</label>

                                <input type="date" name="tanggal"
                                    class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', date('Y-m-d')) }}">

                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Department (readonly, dari user login) --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>

                                <input type="text" class="form-control" value="{{ $department->name }}" readonly>
                            </div>
                        </div>

                        {{-- Cost Center --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>Cost Center</label>

                                <select name="cost_center_id" id="cost_center"
                                    class="form-control @error('cost_center_id') is-invalid @enderror">

                                    <option value="">Pilih Cost Center</option>

                                    @foreach ($costCenterList as $costCenter)
                                        <option value="{{ $costCenter->id }}"
                                            {{ old('cost_center_id') == $costCenter->id ? 'selected' : '' }}>
                                            {{ $costCenter->code }} - {{ $costCenter->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('cost_center_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        {{-- PS GROUP --}}
                        <div class="col-md-3">
                            <div class="form-group">

                                <label>PS Group</label>

                                <select id="ps_group" name="ps_group_id"
                                    class="form-control @error('ps_group_id') is-invalid @enderror">

                                    <option value="">Pilih PS Group</option>

                                </select>

                                @error('ps_group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- IMPORT FILE --}}
            <div class="card">

                <div class="card-header">
                    <h4>Data Daily Activity (Import Excel)</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>File Excel</label>

                                <input type="file" name="file" accept=".xlsx,.xls"
                                    class="form-control @error('file') is-invalid @enderror">

                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted d-block mt-1">
                                    Kolom wajib (baris pertama header): <strong>Nama</strong>, <strong>Produk</strong>,
                                    <strong>Qty individu (kg)</strong>. Kolom opsional: <strong>Lama (jam)</strong>,
                                    <strong>Harga</strong>.
                                </small>
                            </div>
                        </div>

                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-circle-info"></i>
                        1 file bisa berisi banyak karyawan sekaligus &mdash; kolom <strong>Nama</strong> di tiap baris
                        akan otomatis dicocokkan ke master employee, tidak perlu pilih employee manual satu-satu.
                        Nama Produk harus cocok dengan produk yang terdaftar di Cost Center yang dipilih. Harga/KG
                        otomatis diambil dari master produk kalau kolom Harga di Excel kosong.
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Import & Simpan
                    </button>
                </div>

            </div>

        </form>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            if ($('#cost_center').val()) {
                loadPsGroups($('#cost_center').val());
            }
        });

        $('#cost_center').change(function() {

            let costCenterId = $(this).val();

            $('#ps_group').html('<option value="">Pilih PS Group</option>');

            if (!costCenterId) {
                return;
            }

            loadPsGroups(costCenterId);
        });

        function loadPsGroups(costCenterId) {

            $.get(
                "{{ route('daily-activity.ps-groups', ':id') }}".replace(':id', costCenterId),
                function(data) {

                    let html = '<option value="">Pilih PS Group</option>';
                    let oldPsGroup = "{{ old('ps_group_id') }}";

                    $.each(data, function(i, item) {
                        let selected = (oldPsGroup && oldPsGroup == item.id) ? 'selected' : '';
                        html += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                    });

                    $('#ps_group').html(html);
                }
            );
        }
    </script>
@endpush
