@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Generate Payroll OS</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.payroll.outsourcing.index') }}">Payroll OS</a>
            </div>
            <div class="breadcrumb-item">Generate</div>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Generate Payroll Outsourcing</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.payroll.outsourcing.generate') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Bulan</label>
                        <select name="month" class="form-control" required>
                            <option value="">-- Pilih Bulan --</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}"
                                    {{ old('month', now()->month) == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tahun</label>
                        <input type="number" name="year" class="form-control" value="{{ old('year', date('Y')) }}"
                            required>
                    </div>
                </div>

                <div class="alert alert-info">
                    <b>Catatan:</b> Payroll akan digenerate untuk semua karyawan <strong>Outsourcing</strong>
                    berdasarkan data absensi pada periode yang dipilih.
                    Gaji dihitung dari <code>base_salary : hari_standar x hari_masuk</code>.
                    Jika sudah ada data DRAFT, akan diupdate. Data FINAL tidak akan diubah.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cogs"></i> Generate Payroll
                </button>
                <a href="{{ route('admin.payroll.outsourcing.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
