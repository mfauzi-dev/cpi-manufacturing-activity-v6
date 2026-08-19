@extends('layouts.master')

@section('content')
    <div class="section-header">
        <a href="{{ route('admin.daily-activity.index') }}" class="text-dark mr-2">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="d-inline">{{ $costCenter->code }} - {{ $costCenter->name }}</h1>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('admin.daily-activity.detail', $costCenter->id) }}">

                    <div class="row align-items-end">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                        </div>
                    </div>


                    <div class="mt-2">

                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('admin.daily-activity.detail', $costCenter->id) }}" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        {{-- TABEL DETAIL --}}
        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Material</th>
                            <th>Kode Material</th>
                            <th>Yang Input</th>
                            <th class="text-right">Kg</th>
                            <th class="text-right">Harga/kg</th>
                            <th class="text-right">Rupiah</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($details as $key => $detail)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($detail->tanggal)->translatedFormat('d M Y') }}</td>
                                <td>{{ $detail->material_code }}</td>
                                <td>{{ $detail->material_name }}</td>
                                <td>{{ $detail->input_by }}</td>
                                <td class="text-right">{{ number_format($detail->total_kg, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($detail->harga_per_kg, 0, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($detail->total_harga, 0, ',', '.') }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data pada rentang tanggal ini</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>

                <div class="card-footer text-right">
                    {{ $details->withQueryString()->links() }}
                </div>

            </div>
        </div>

    </div>
@endsection
