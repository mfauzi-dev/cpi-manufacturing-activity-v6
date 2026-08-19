@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Edit Absensi</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('admin.attendance.index') }}">Absensi</a>
            </div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Edit Absensi</h4>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    {{-- KARYAWAN (readonly, tidak bisa ganti) --}}
                    <div class="form-group">
                        <label>Karyawan</label>
                        <input type="text" class="form-control" readonly
                            value="{{ $attendance->employee->name }} - {{ $attendance->employee->nik }}">
                    </div>

                    {{-- DATE --}}
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="attendance_date"
                            value="{{ old('attendance_date', \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d')) }}"
                            class="form-control @error('attendance_date') is-invalid @enderror">
                        @error('attendance_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DEPARTMENT --}}
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                            @foreach ($departmentList as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $attendance->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="HADIR" {{ old('status', $attendance->status) == 'HADIR' ? 'selected' : '' }}>✓
                                Hadir</option>
                            <option value="ABSEN" {{ old('status', $attendance->status) == 'ABSEN' ? 'selected' : '' }}>✗
                                Absen</option>
                            <option value="SAKIT" {{ old('status', $attendance->status) == 'SAKIT' ? 'selected' : '' }}>
                                Sakit</option>
                            <option value="IZIN" {{ old('status', $attendance->status) == 'IZIN' ? 'selected' : '' }}>
                                Izin</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NOTES --}}
                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="notes" value="{{ old('notes', $attendance->notes) }}"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Contoh: izin keperluan keluarga...">
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
