@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Employee Daily Earnings</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.daily-earning.index') }}">Daily Earnings</a>
            </div>
            <div class="breadcrumb-item">Edit</div>
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

    @if (session()->has('danger'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('danger') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Edit Daily Earning</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.daily-earning.update', $dailyEarning->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    {{-- EMPLOYEE --}}
                    <div class="form-group">
                        <label>Karyawan</label>
                        <select name="employee_id"
                            class="form-control select2-ajax @error('employee_id') is-invalid @enderror">
                            <option value="{{ $dailyEarning->employee->id }}" selected>
                                {{ $dailyEarning->employee->name }} -
                                {{ $dailyEarning->employee->nik }}
                            </option>
                        </select>

                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DATE --}}
                    <div class="form-group">
                        <label>Tanggal Kerja</label>
                        <input type="date" name="work_date" value="{{ old('work_date', $dailyEarning->work_date) }}"
                            class="form-control @error('work_date') is-invalid @enderror">

                        @error('work_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DEPARTMENT --}}
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">-- Pilih Department --</option>

                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $dailyEarning->product->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PRODUCT --}}
                    <div class="form-group">
                        <label>Produk</label>
                        <select name="product_id" id="product_id"
                            class="form-control @error('product_id') is-invalid @enderror">
                            <option value="{{ $dailyEarning->product->id }}"
                                data-amount="{{ $dailyEarning->product->amount }}" selected>
                                {{ $dailyEarning->product->name }}
                            </option>
                        </select>

                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- AMOUNT --}}
                    <input type="hidden" name="amount" id="amount" value="{{ old('amount', $dailyEarning->amount) }}">

                    {{-- KG --}}
                    <div class="form-group">
                        <label>KG (opsional)</label>
                        <input type="number" name="kg" value="{{ old('kg', (int) $dailyEarning->kg) }}"
                            class="form-control @error('kg') is-invalid @enderror" placeholder="Masukkan total kg...">

                        @error('kg')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-warning">
                        Update
                    </button>
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

            function loadProducts(deptId, selectedProductId = null) {

                const $product = $('#product_id');

                if (!deptId) {
                    $product.html('<option value="">-- Pilih Department dulu --</option>');
                    return;
                }

                $.get(`/products-by-department/${deptId}`, function(products) {

                    $product.html('<option value="">-- Pilih Produk --</option>');

                    products.forEach(p => {

                        let selected = selectedProductId == p.id ? 'selected' : '';

                        $product.append(`
                    <option value="${p.id}"
                        data-amount="${p.amount}"
                        ${selected}>
                        ${p.name}
                    </option>
                `);

                    });

                    $('#product_id').trigger('change');
                });
            }

            // Load produk saat halaman edit dibuka
            loadProducts(
                "{{ $dailyEarning->product->department_id }}",
                "{{ $dailyEarning->product_id }}"
            );

            $('#department_id').on('change', function() {
                loadProducts($(this).val());
            });

            $('#product_id').on('change', function() {
                const amount = $(this).find(':selected').data('amount') ?? '';
                $('#amount').val(amount);
            });

        });
    </script>
@endpush
