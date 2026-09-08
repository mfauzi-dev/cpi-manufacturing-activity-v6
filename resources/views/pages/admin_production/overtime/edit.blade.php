@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Edit Overtime</h1>
    </div>

    <div class="section-body">

        <form action="{{ route('admin-production.overtime.update', $overtime->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                                    data-level-id="{{ $employee->level_id ?? '' }}"
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
                    <h4>Data Overtime</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

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


                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Jenis Overtime <span class="text-danger">*</span>
                                </label>

                                <select name="overtime_type" id="overtime_type" class="form-control" required>

                                    <option value="OTL1"
                                        {{ old('overtime_type', $overtime->overtime_type) === 'OTL1' ? 'selected' : '' }}>
                                        OTL1 - Overtime Hari Biasa Bulan Lalu
                                    </option>

                                    <option value="OTL2"
                                        {{ old('overtime_type', $overtime->overtime_type) === 'OTL2' ? 'selected' : '' }}>
                                        OTL2 - Overtime Hari Libur Bulan Lalu
                                    </option>

                                    <option value="OT01"
                                        {{ old('overtime_type', $overtime->overtime_type) === 'OT01' ? 'selected' : '' }}>
                                        OT01 - Overtime Hari Biasa Bulan Ini
                                    </option>

                                    <option value="OT02"
                                        {{ old('overtime_type', $overtime->overtime_type) === 'OT02' ? 'selected' : '' }}>
                                        OT02 - Overtime Hari Libur Bulan Ini
                                    </option>

                                </select>

                                <small class="text-muted">
                                    Terisi otomatis berdasarkan tanggal (bulan berjalan/bulan lalu & hari
                                    biasa/libur). Bisa diubah manual jika perlu.
                                </small>

                                @error('overtime_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>

                        </div>

                    </div>


                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Jam Mulai <span class="text-danger">*</span>
                                </label>

                                <input type="time" name="start_time" id="start_time" class="form-control"
                                    value="{{ old('start_time', $overtime->start_time) }}" required>

                                @error('start_time')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Jam Selesai <span class="text-danger">*</span>
                                </label>

                                <input type="time" name="end_time" id="end_time" class="form-control"
                                    value="{{ old('end_time', $overtime->end_time) }}" required>

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
                                        value="{{ old('total_hours_actual', $overtime->total_hours_actual) }}"
                                        placeholder="Masukkan total jam" required>

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
                                        placeholder="Masukkan jam konversi" required>

                                    <div class="input-group-append">
                                        <span class="input-group-text">Jam</span>
                                    </div>

                                </div>

                                <small class="text-muted">
                                    Umum: OTL1/OT01 = 1,5x, OTL2/OT02 = 2x.
                                    Level 4 & status CPI: jam konversi = jam actual (1x).
                                    Level 5/6 & status CPI, atau status harian/harian kontrak:
                                    berlaku rumus Jam Lemur Berjalan.
                                    Nilai dapat diubah manual.
                                </small>

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

                                <small class="text-muted" id="rate-info">
                                    Rate berdasarkan data karyawan.
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
                                    Dihitung berdasarkan rate overtime dan total jam konversi.
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

                    <a href="{{ route('admin-production.overtime.index') }}" class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>
                        Kembali

                    </a>

                    <button type="submit" class="btn btn-primary">

                        <i class="fas fa-save"></i>
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


            let rateRequestToken = 0;

            let holidayDates = @json($holidays);


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

                    $('#rate-info').text(
                        'Pilih karyawan untuk melihat rate overtime.'
                    );

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

                let levelId = parseInt(option.data('level-id')) || 0;


                let isOneToOne =
                    levelId === 4 &&
                    status === 'cpi';


                let isProgressive =
                    ((levelId === 5 || levelId === 6) && status === 'cpi') ||
                    status === 'harian' ||
                    status === 'harian_kontrak';


                if (isOneToOne) {

                    $('#rate-info').text(
                        'Karyawan CPI level 4: jam konversi = jam actual (1x, tanpa pembobotan).'
                    );

                } else if (isProgressive) {

                    $('#rate-info').text(
                        'Konversi jam mengikuti rumus Jam Lemur Berjalan (progresif per jam).'
                    );

                } else if (levelId === 5 || levelId === 6) {

                    $('#rate-info').text(
                        'Rate berdasarkan gaji pokok / 173 untuk level 5 dan 6.'
                    );

                } else {

                    $('#rate-info').text(
                        'Rate berdasarkan tahun, status karyawan, dan level.'
                    );

                }

            }


            function autoDetectOvertimeType() {

                let dateVal = $('#date').val();

                if (!dateVal) {
                    return;
                }


                let selectedDate = new Date(dateVal + 'T00:00:00');

                let today = new Date();


                let isSameMonth =
                    selectedDate.getFullYear() === today.getFullYear() &&
                    selectedDate.getMonth() === today.getMonth();


                let day = selectedDate.getDay();

                let isSundayHoliday = day === 0;

                let isNationalHoliday = holidayDates.includes(dateVal);

                let isHoliday = isSundayHoliday || isNationalHoliday;


                let type;

                if (isSameMonth) {

                    type = isHoliday ? 'OT02' : 'OT01';

                } else {

                    type = isHoliday ? 'OTL2' : 'OTL1';

                }


                $('#overtime_type').val(type);

            }


            function isEligibleForProgressiveOvertime() {

                let option = $('#employee_id option:selected');

                let levelId = parseInt(option.data('level-id')) || 0;

                let status = String(option.data('status') || '').toLowerCase();


                let isCpiLevel56 =
                    (levelId === 5 || levelId === 6) &&
                    status === 'cpi';


                let isHarianAnyLevel =
                    status === 'harian' ||
                    status === 'harian_kontrak';


                return isCpiLevel56 || isHarianAnyLevel;

            }


            function isEligibleForOneToOneConversion() {

                let option = $('#employee_id option:selected');

                let levelId = parseInt(option.data('level-id')) || 0;

                let status = String(option.data('status') || '').toLowerCase();


                return levelId === 4 && status === 'cpi';

            }


            function calculateWeightedHours(totalHoursActual, isHoliday) {

                let remaining = totalHoursActual;

                let weighted = 0;


                if (!isHoliday) {

                    // Hari kerja biasa
                    // Jam ke-1 = 1,5x
                    let firstHour = Math.min(remaining, 1);

                    weighted += firstHour * 1.5;

                    remaining -= firstHour;


                    // Jam ke-2 dan seterusnya = 2x
                    if (remaining > 0) {

                        weighted += remaining * 2;

                    }

                } else {

                    // Hari libur / akhir pekan
                    // Jam ke-1 s.d. jam ke-8 = 2x
                    let firstEight = Math.min(remaining, 8);

                    weighted += firstEight * 2;

                    remaining -= firstEight;


                    // Jam ke-9 dan seterusnya = 3x
                    if (remaining > 0) {

                        weighted += remaining * 3;

                    }

                }


                return weighted;

            }


            function setDefaultConversion() {

                let totalHoursActual =
                    parseFloat($('#total_hours_actual').val()) || 0;

                let overtimeType =
                    $('#overtime_type').val();


                if (totalHoursActual <= 0) {

                    loadOvertimeRate();

                    return;

                }


                let isHoliday =
                    overtimeType === 'OTL2' ||
                    overtimeType === 'OT02';


                let totalHoursKonversi;


                if (isEligibleForOneToOneConversion()) {

                    // Level 4 CPI
                    totalHoursKonversi =
                        totalHoursActual;

                } else if (isEligibleForProgressiveOvertime()) {

                    // Level 5/6 CPI atau harian/harian kontrak
                    totalHoursKonversi =
                        calculateWeightedHours(
                            totalHoursActual,
                            isHoliday
                        );

                } else {

                    // Karyawan lainnya
                    let multiplier =
                        isHoliday ? 2 : 1.5;

                    totalHoursKonversi =
                        totalHoursActual * multiplier;

                }


                $('#total_hours_konversi').val(
                    totalHoursKonversi.toFixed(2)
                );


                loadOvertimeRate();

            }


            function loadOvertimeRate() {

                let employeeId =
                    $('#employee_id').val();

                let date =
                    $('#date').val();

                let totalHoursActual =
                    $('#total_hours_actual').val() || 0;

                let totalHoursKonversi =
                    $('#total_hours_konversi').val() || 0;


                if (!employeeId ||
                    !date ||
                    !totalHoursKonversi) {

                    $('#hourly_rate_display').val('0');

                    $('#overtime_amount_display').val('0');

                    return;

                }


                $('#hourly_rate_display').val('Loading...');

                $('#overtime_amount_display').val('Loading...');


                let currentToken =
                    ++rateRequestToken;


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

                            let rate =
                                parseFloat(response.rate) || 0;

                            let amount =
                                parseFloat(response.amount) || 0;


                            $('#hourly_rate_display').val(
                                formatRupiah(rate)
                            );


                            $('#overtime_amount_display').val(
                                formatRupiah(amount)
                            );

                        } else {

                            $('#hourly_rate_display').val('0');

                            $('#overtime_amount_display').val('0');


                            if (response.message) {

                                $('#rate-info').text(
                                    response.message
                                );

                            }

                        }

                    },


                    error: function(xhr) {

                        if (currentToken !== rateRequestToken) {
                            return;
                        }


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

                setDefaultConversion();

            });


            $('#date').on('change', function() {

                autoDetectOvertimeType();

                setDefaultConversion();

            });


            $('#overtime_type').on('change', function() {

                setDefaultConversion();

            });


            $('#total_hours_actual').on('input', function() {

                setDefaultConversion();

            });


            $('#total_hours_konversi').on('input', function() {

                loadOvertimeRate();

            });


            // Initial load
            updateEmployeeDetail();

            autoDetectOvertimeType();


            if ($('#total_hours_actual').val()) {

                setDefaultConversion();

            } else {

                loadOvertimeRate();

            }

        });
    </script>
@endpush
