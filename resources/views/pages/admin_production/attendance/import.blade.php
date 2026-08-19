@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Import Absensi - {{ $area->name }}</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                <form method="POST" action="{{ route('admin.attendance.upload') }}" enctype="multipart/form-data">

                    @csrf

                    <input type="hidden" name="area_id" value="{{ $area->id }}">

                    <div class="form-group">
                        <label>Tanggal Absensi</label>
                        <input type="date" name="attendance_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>File Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary">
                            Upload & Import
                        </button>

                        <a href="{{ route('admin.attendance.detail', $area->id) }}" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection
