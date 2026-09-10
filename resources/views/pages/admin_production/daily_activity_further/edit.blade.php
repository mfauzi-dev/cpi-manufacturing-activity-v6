@extends('layouts.master')

@section('content')
    ```
    <div class="section-header">
        <h1>Edit Daily Activity Further</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin-production.daily-activity-further.index') }}">
                    Daily Activity Further
                </a>
            </div>

            <div class="breadcrumb-item">
                Edit
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">

        <div class="card-header">
            <h4>Edit Daily Activity Further</h4>
        </div>

        <form action="{{ route('admin-production.daily-activity-further.update', $detail->id) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="card-body">

                <div class="form-group">
                    <label>Tanggal</label>

                    <input type="date" name="tanggal"
                        value="{{ old('tanggal', \Carbon\Carbon::parse($detail->dailyActivityFurther->tanggal)->format('Y-m-d')) }}"
                        class="form-control @error('tanggal') is-invalid @enderror">

                    @error('tanggal')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Department</label>

                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivityFurther->department->name ?? '-' }}" readonly>
                </div>

                <div class="form-group">
                    <label>Cost Center</label>

                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivityFurther->costCenter->name ?? '-' }}" readonly>

                    <input type="hidden" name="cost_center_id" value="{{ $detail->dailyActivityFurther->cost_center_id }}">
                </div>

                <div class="form-group">
                    <label>Group</label>

                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivityFurther->psGroup->name ?? '-' }}" readonly>

                    <input type="hidden" name="ps_group_id" value="{{ $detail->dailyActivityFurther->ps_group_id }}">
                </div>

                <div class="form-group">
                    <label>Line</label>

                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivityFurther->line->name ?? '-' }}" readonly>

                    <input type="hidden" name="line_id" value="{{ $detail->dailyActivityFurther->line_id }}">
                </div>

                <div class="form-group">
                    <label>Karyawan</label>

                    <input type="text" class="form-control"
                        value="{{ $detail->dailyActivityFurther->employee->name ?? '-' }} - {{ $detail->dailyActivityFurther->employee->nik ?? '-' }}"
                        readonly>

                    <input type="hidden" name="employee_id" value="{{ $detail->dailyActivityFurther->employee_id }}">
                </div>

                <div class="form-group">
                    <label>Nama Material</label>

                    <select name="product_id" id="product_id"
                        class="form-control @error('product_id') is-invalid @enderror">

                        @foreach ($productList as $product)
                            <option value="{{ $product->id }}"
                                {{ old('product_id', $detail->product_id) == $product->id ? 'selected' : '' }}>

                                {{ $product->material_name }}

                                @if ($product->material_code)
                                    - {{ $product->material_code }}
                                @endif

                            </option>
                        @endforeach

                    </select>

                    @error('product_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Production RM</label>

                    <input type="number" name="total_kg_rm" id="total_kg_rm" step="0.01" min="0"
                        value="{{ old('total_kg_rm', $detail->total_kg_rm ?? 0) }}"
                        class="form-control @error('total_kg_rm') is-invalid @enderror"
                        placeholder="Masukkan Production RM...">

                    @error('total_kg_rm')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Production FG</label>

                    <input type="number" name="total_kg_fg" id="total_kg_fg" step="0.01" min="0"
                        value="{{ old('total_kg_fg', $detail->total_kg_fg ?? 0) }}"
                        class="form-control @error('total_kg_fg') is-invalid @enderror"
                        placeholder="Masukkan Production FG...">

                    @error('total_kg_fg')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Total KG</label>

                    <input type="text" id="total_kg_display" class="form-control" value="0,00" readonly>

                    <small class="form-text text-muted">
                        Total KG dihitung otomatis dari Production RM + Production FG.
                    </small>
                </div>

                <div class="form-group">
                    <label>Man Hours</label>

                    <input type="number" name="man_power" id="man_power" step="0.01" min="0"
                        value="{{ old('man_power', $detail->man_power ?? 0) }}"
                        class="form-control @error('man_power') is-invalid @enderror" placeholder="Masukkan man hours...">

                    @error('man_power')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Productivity</label>

                    <input type="text" id="productivity_display" class="form-control"
                        value="{{ number_format($detail->productivity ?? 0, 2, ',', '.') }}" readonly>

                    <small class="form-text text-muted">
                        Productivity dihitung otomatis dari Total KG ÷ Man Hours.
                    </small>
                </div>

            </div>

            <div class="card-footer text-right">

                <a href="{{ url()->previous() }}" class="btn btn-secondary">

                    Batal

                </a>

                <button type="submit" class="btn btn-warning">

                    <i class="fas fa-save"></i>
                    Update

                </button>

            </div>

        </form>

    </div>
    ```
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function hitungProductivity() {

                let rm = parseFloat($('#total_kg_rm').val()) || 0;
                let fg = parseFloat($('#total_kg_fg').val()) || 0;
                let manHours = parseFloat($('#man_power').val()) || 0;

                let totalKg = rm + fg;

                $('#total_kg_display').val(
                    totalKg.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );

                if (manHours > 0) {

                    let productivity = totalKg / manHours;

                    $('#productivity_display').val(
                        productivity.toLocaleString('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })
                    );

                } else {

                    $('#productivity_display').val('0,00');

                }

            }

            $('#total_kg_rm, #total_kg_fg, #man_power').on(
                'keyup change',
                function() {

                    hitungProductivity();

                }
            );

            hitungProductivity();

        });
    </script>
@endpush
