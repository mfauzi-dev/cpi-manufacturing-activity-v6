@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Generate Payroll</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.payroll.borongan.index') }}">Payroll</a></div>
            <div class="breadcrumb-item">Generate</div>
        </div>
    </div>

    {{-- FLASH MESSAGE --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- FORM --}}
    <div class="card">
        <div class="card-header">
            <h4>Generate Payroll Periode</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.payroll.borongan.generate') }}" method="POST">
                @csrf

                <div class="row">

                    {{-- MONTH --}}
                    <div class="col-md-6 mb-3">
                        <label>Month</label>
                        <select name="month" class="form-control" required>
                            <option value="">-- Select Month --</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- YEAR --}}
                    <div class="col-md-6 mb-3">
                        <label>Year</label>
                        <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                    </div>

                </div>

                {{-- INFO BOX --}}
                <div class="alert alert-info">
                    <b>Note:</b> Payroll akan digenerate untuk semua karyawan aktif pada periode yang dipilih.
                    Jika sudah ada data, akan diupdate (tidak double).
                </div>

                {{-- BUTTON --}}
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cogs"></i> Generate Payroll
                </button>

                <a href="{{ route('admin.payroll.borongan.index') }}" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>
    </div>
@endsection
