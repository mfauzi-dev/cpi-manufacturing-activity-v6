@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Dashboard</h1>
    </div>

    <div class="section-body">
        <div class="alert alert-info">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong> (Admin Absensi)
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <p class="mb-0">
                            Silakan gunakan menu <strong>Absensi</strong> di sidebar untuk mengelola data kehadiran
                            karyawan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
