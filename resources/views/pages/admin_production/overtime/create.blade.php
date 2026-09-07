@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Tambah Overtime</h1>
    </div>

    <div class="section-body">
        <form action="{{ route('admin-production.overtime.store') }}" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h4>Data Karyawan</h4>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label>
                            Karyawan <span class="text-danger">*</span>
                        </label>

                        <select name="employee_id" id="employee_id" class="form-control select2" required>
                            <option value="">-- Pilih Karyawan --</option>

                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    data-department="{{ $employee->department?->name ?? '-' }}"
                                    data-cost-center="{{ $employee->costCenter ? $employee->costCenter->code . ' - ' . $employee->costCenter->name : '-' }}"
                                    data-ps-group="{{ $employee->psGroup?->name ?? '-' }}"
                                    data-position="{{ $employee->position?->name ?? '-' }}"
                                    data-level="{{ $employee->level?->name ?? '-' }}"
                                    data-status="{{ $employee->employee_status ?? '-' }}">
                                    {{ $employee->nik ?? '-' }} - {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('employee_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div id="employee-detail" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" id="department" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cost Center</label>
                                    <input type="text" id="cost_center" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PS Group</label>
                                    <input type="text" id="ps_group" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Position</label>
                                    <input type="text" id="position" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Level</label>
                                    <input type="text" id="level" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Karyawan</label>
                                    <input type="text" id="employee_status" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Overtime</h4>
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Tanggal <span class="text-danger">*</span>
                                </label>

                                <input type="date" name="date" id="date" class="form-control"
                                    value="{{ old('date', now()->format('Y-m-d')) }}" required>

                                @error('date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Jam Mulai <span class="text-danger">*</span>
                                </label>

                                <input type="time" name="start_time" id="start_time" class="form-control"
                                    value="{{ old('start_time') }}" required>

                                @error('start_time')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Jam Selesai <span class="text-danger">*</span>
                                </label>

                                <input type="time" name="end_time" id="end_time" class="form-control"
                                    value="{{ old('end_time') }}" required>

                                @error('end_time')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Total Jam Actual <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <input type="number" name="total_hours_actual" id="total_hours_actual"
                                        class="form-control" step="0.01" min="0"
                                        value="{{ old('total_hours_actual') }}" required>

                                    <div class="input-group-append">
                                        <span class="input-group-text">Jam</span>
                                    </div>
                                </div>

                                @error('total_hours_actual')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Total Jam Konversi <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <input type="number" name="total_hours_konversi" id="total_hours_konversi"
                                        class="form-control" step="0.01" min="0"
                                        value="{{ old('total_hours_konversi') }}" required>

                                    <div class="input-group-append">
                                        <span class="input-group-text">Jam</span>
                                    </div>
                                </div>

                                @error('total_hours_konversi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Rate Overtime</label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>

                                    <input type="text" id="hourly_rate_display" class="form-control" value="0"
                                        readonly>
                                </div>

                                <small class="text-muted" id="rate-info">
                                    Pilih karyawan untuk melihat rate overtime.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Total Overtime</label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>

                                    <input type="text" id="overtime_amount_display" class="form-control"
                                        value="0" readonly>
                                </div>

                                <small class="text-muted">
                                    Dihitung otomatis oleh sistem berdasarkan status karyawan, level, dan jam overtime.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Keterangan</label>

                                <textarea name="description" class="form-control" rows="3" placeholder="Keterangan overtime...">{{ old('description') }}</textarea>

                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin-production.overtime.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Overtime
                    </button>
                </div>
            </div>

        </form>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#employee_id').select2({
                width: '100%',
                placeholder: '-- Pilih Karyawan --',
                allowClear: true
            });
            let rateRequestToken = 0;

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function updateEmployeeDetail() {
                let option = $('#employee_id option:selected');
                if (!option.val()) {
                    $('#employee-detail').hide();
                    $('#department').val('');
                    $('#cost_center').val('');
                    $('#ps_group').val('');
                    $('#position').val('');
                    $('#level').val('');
                    $('#employee_status').val('');
                    $('#rate-info').text('Pilih karyawan untuk melihat rate overtime.');
                    return;
                }
                $('#department').val(option.data('department'));
                $('#cost_center').val(option.data('cost-center'));
                $('#ps_group').val(option.data('ps-group'));
                $('#position').val(option.data('position'));
                $('#level').val(option.data('level'));
                $('#employee_status').val(option.data('status'));
                $('#employee-detail').show();
                let status = String(option.data('status') || '').toLowerCase();
                let level = parseInt(option.data('level')) || 0;
                if (status === 'harian') {
                    $('#rate-info').text(
                        'Rate berdasarkan UMP / 173. Level tidak berpengaruh untuk karyawan harian.');
                } else if (level === 5 || level === 6) {
                    $('#rate-info').text('Rate berdasarkan gaji pokok / 173 untuk level 5 dan 6.');
                } else {
                    $('#rate-info').text('Rate berdasarkan tahun, status karyawan, dan level.');
                }
            }

            function loadOvertimeRate() {
                let employeeId = $('#employee_id').val();
                let date = $('#date').val();
                let totalHoursActual = $('#total_hours_actual').val() || 0;
                let totalHoursKonversi = $('#total_hours_konversi').val() || 0;
                if (!employeeId || !date) {
                    $('#hourly_rate_display').val('0');
                    $('#overtime_amount_display').val('0');
                    return;
                }
                $('#hourly_rate_display').val('Loading...');
                $('#overtime_amount_display').val('Loading...');
                let currentToken = ++rateRequestToken;
                $.ajax({
                    url: "{{ url('/admin-production/overtime/rate') }}/" + employeeId,
                    type: "GET",
                    data: {
                        date: date,
                        total_hours_actual: totalHoursActual,
                        total_hours_konversi: totalHoursKonversi
                    },
                    success: function(response) {
                        if (currentToken !== rateRequestToken) {
                            return;
                        }
                        if (response.success) {
                            let rate = parseFloat(response.rate) || 0;
                            let amount = parseFloat(response.amount) || 0;
                            $('#hourly_rate_display').val(formatRupiah(rate));
                            $('#overtime_amount_display').val(formatRupiah(amount));
                        } else {
                            $('#hourly_rate_display').val('0');
                            $('#overtime_amount_display').val('0');
                        }
                    },
                    error: function(xhr) {
                        if (currentToken !== rateRequestToken) {
                            return;
                        }
                        $('#hourly_rate_display').val('0');
                        $('#overtime_amount_display').val('0');
                        console.log('Error mengambil rate:', xhr.responseText);
                    }
                });
            }
            $('#employee_id').on('change', function() {
                updateEmployeeDetail();
                loadOvertimeRate();
            });
            $('#date').on('change', function() {
                loadOvertimeRate();
            });
            $('#total_hours_actual, #total_hours_konversi').on('input', function() {
                loadOvertimeRate();
            });
            updateEmployeeDetail();
            loadOvertimeRate();
        });
    </script>
@endpush
