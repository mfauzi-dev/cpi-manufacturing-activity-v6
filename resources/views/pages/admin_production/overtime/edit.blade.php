@extends('layouts.master')

@section('content')

    <div class="section-header">
        <h1>Edit Overtime</h1>
    </div>

    <div class="section-body">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle mr-1"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin-production.overtime.update', $overtime->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-user-clock mr-2"></i>
                        Data Karyawan
                    </h4>
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
                                    data-status="{{ $employee->employee_status ?? '-' }}"
                                    {{ old('employee_id', $overtime->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->nik ?? '-' }} - {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('employee_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div id="employee-detail">
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
                    <h4>
                        <i class="fas fa-clock mr-2"></i>
                        Data Overtime
                    </h4>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Tanggal <span class="text-danger">*</span>
                                </label>

                                <input type="date" name="date" id="date" class="form-control"
                                    value="{{ old('date', \Carbon\Carbon::parse($overtime->date)->format('Y-m-d')) }}"
                                    required>

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
                                    value="{{ old('start_time', \Carbon\Carbon::parse($overtime->start_time)->format('H:i')) }}"
                                    required>

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
                                    value="{{ old('end_time', \Carbon\Carbon::parse($overtime->end_time)->format('H:i')) }}"
                                    required>

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
                                        value="{{ old('total_hours_actual', $overtime->total_hours_actual) }}" required>

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
                                        value="{{ old('total_hours_konversi', $overtime->total_hours_konversi) }}"
                                        required>

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

                                    <input type="text" id="hourly_rate_display" class="form-control"
                                        value="{{ number_format($overtime->hourly_rate, 0, ',', '.') }}" readonly>
                                </div>

                                <small class="text-muted">
                                    Harian: UMP / 173. Level 5/6: Basic Salary / 173.
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
                                        value="{{ number_format($overtime->overtime_amount, 0, ',', '.') }}" readonly>
                                </div>

                                <small class="text-muted">
                                    Harian dan level 5/6 menggunakan Total Jam Konversi.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Keterangan</label>

                                <textarea name="description" class="form-control" rows="3" placeholder="Keterangan overtime...">{{ old('description', $overtime->description) }}</textarea>

                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin-production.overtime.show', $overtime->id) }}"
                        class="btn btn-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Update Overtime
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

            let overtimeRate = {{ (float) $overtime->hourly_rate }};

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function updateEmployeeDetail() {
                let option = $('#employee_id option:selected');

                if (!option.val()) {
                    $('#department').val('');
                    $('#cost_center').val('');
                    $('#ps_group').val('');
                    $('#position').val('');
                    $('#level').val('');
                    $('#employee_status').val('');
                    return;
                }

                $('#department').val(option.data('department'));
                $('#cost_center').val(option.data('cost-center'));
                $('#ps_group').val(option.data('ps-group'));
                $('#position').val(option.data('position'));
                $('#level').val(option.data('level'));
                $('#employee_status').val(option.data('status'));
            }

            function calculateOvertime() {
                let actual = parseFloat($('#total_hours_actual').val()) || 0;
                let konversi = parseFloat($('#total_hours_konversi').val()) || 0;
                let status = ($('#employee_id option:selected').data('status') || '').toLowerCase();
                let level = parseInt($('#employee_id option:selected').data('level')) || 0;

                let hours = actual;

                if (status === 'harian' || [5, 6].includes(level)) {
                    hours = konversi;
                }

                let amount = hours * overtimeRate;

                $('#overtime_amount_display').val(
                    formatRupiah(amount)
                );
            }

            function loadOvertimeRate() {
                let employeeId = $('#employee_id').val();
                let date = $('#date').val();
                let actual = parseFloat($('#total_hours_actual').val()) || 0;
                let konversi = parseFloat($('#total_hours_konversi').val()) || 0;

                if (!employeeId || !date) {
                    overtimeRate = 0;
                    $('#hourly_rate_display').val('0');
                    $('#overtime_amount_display').val('0');
                    return;
                }

                $('#hourly_rate_display').val('Loading...');

                $.ajax({
                    url: "{{ url('/admin-production/overtime/rate') }}/" + employeeId,
                    type: "GET",
                    data: {
                        date: date,
                        total_hours_actual: actual,
                        total_hours_konversi: konversi
                    },
                    success: function(response) {
                        console.log('Rate Response:', response);

                        if (response.success) {
                            overtimeRate = parseFloat(response.rate) || 0;

                            $('#hourly_rate_display').val(
                                formatRupiah(overtimeRate)
                            );

                            $('#overtime_amount_display').val(
                                formatRupiah(parseFloat(response.amount) || 0)
                            );
                        } else {
                            overtimeRate = 0;

                            $('#hourly_rate_display').val('0');
                            $('#overtime_amount_display').val('0');

                            console.log(
                                'Rate tidak ditemukan:',
                                response.message
                            );
                        }
                    },
                    error: function(xhr) {
                        overtimeRate = 0;

                        $('#hourly_rate_display').val('0');
                        $('#overtime_amount_display').val('0');

                        console.log(
                            'Error mengambil rate:',
                            xhr.responseText
                        );
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

            $('#total_hours_actual').on('input', function() {
                loadOvertimeRate();
            });

            $('#total_hours_konversi').on('input', function() {
                loadOvertimeRate();
            });

            updateEmployeeDetail();
            loadOvertimeRate();
        });
    </script>
@endpush
