@extends('layouts.master')

@section('content')

    <div class="section-header">

        <h1>Overtime Rate</h1>

        <div class="section-header-breadcrumb">

            <div class="breadcrumb-item active">
                <a href="{{ route('manager.overtime-rate.index') }}">
                    Overtime Rate
                </a>
            </div>

            <div class="breadcrumb-item">
                Edit
            </div>

        </div>

    </div>

    <div class="section-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">

            <div class="card-header">
                <h4>Edit Overtime Rate</h4>
            </div>

            <form action="{{ route('manager.overtime-rate.update', $overtimeRate->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label>Tahun</label>

                        <select name="tahun" class="form-control @error('tahun') is-invalid @enderror">

                            <option value="">
                                Pilih Tahun
                            </option>

                            @for ($year = date('Y') + 1; $year >= date('Y') - 5; $year--)
                                <option value="{{ $year }}"
                                    {{ old('tahun', $overtimeRate->tahun) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor

                        </select>

                        @error('tahun')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">
                        <label>Employee Status</label>

                        <select name="employee_status" class="form-control @error('employee_status') is-invalid @enderror">

                            <option value="">
                                Pilih Employee Status
                            </option>

                            <option value="cpi"
                                {{ old('employee_status', $overtimeRate->employee_status) === 'cpi' ? 'selected' : '' }}>
                                CPI
                            </option>

                            <option value="borongan"
                                {{ old('employee_status', $overtimeRate->employee_status) === 'borongan' ? 'selected' : '' }}>
                                Borongan
                            </option>

                            <option value="harian"
                                {{ old('employee_status', $overtimeRate->employee_status) === 'harian' ? 'selected' : '' }}>
                                Harian
                            </option>

                        </select>

                        @error('employee_status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">
                        <label>Level</label>

                        <select name="level_id" class="form-control @error('level_id') is-invalid @enderror">

                            <option value="">
                                Pilih Level
                            </option>

                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}"
                                    {{ old('level_id', $overtimeRate->level_id) == $level->id ? 'selected' : '' }}>
                                    {{ $level->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('level_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">
                        <label>Position</label>

                        <select name="position_id" class="form-control @error('position_id') is-invalid @enderror">

                            <option value="">
                                Pilih Position
                            </option>

                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}"
                                    {{ old('position_id', $overtimeRate->position_id) == $position->id ? 'selected' : '' }}>
                                    {{ $position->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('position_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-group">
                        <label>Rate</label>

                        <input type="number" name="rate" class="form-control @error('rate') is-invalid @enderror"
                            value="{{ old('rate', $overtimeRate->rate) }}" placeholder="Masukkan rate overtime"
                            min="0" step="0.01">

                        @error('rate')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="card-footer text-right">

                    <a href="{{ route('manager.overtime-rate.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection
