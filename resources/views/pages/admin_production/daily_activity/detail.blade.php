@extends('layouts.master')

@section('content')
    <div class="section-header">
        <a href="{{ route('admin-production.daily-activity.index') }}" class="text-dark mr-2">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="d-inline">{{ $costCenter->code }} - {{ $costCenter->name }}</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET"
                    action="{{ route('admin-production.daily-activity.detail', [
                        'costCenter' => $costCenter->id,
                        'psGroup' => $psGroup->id,
                    ]) }}">

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

                        <a href="{{ route('admin-production.daily-activity.detail', [
                            'costCenter' => $costCenter->id,
                            'psGroup' => $psGroup->id,
                        ]) }}"
                            class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        {{-- TABEL DETAIL --}}
        <div class="card">
            <div class="card-body table-responsive">

                <div class="mb-2">

                    <a href="{{ route('daily-activity.export-excel', [
                        'costCenterId' => $costCenter->id,
                        'psGroupId' => $psGroup->id,
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                    ]) }}"
                        class="btn btn-success">
                        <i class="fas fa-file-excel"></i>
                        Excel
                    </a>

                    <a href="{{ route('daily-activity.export-pdf', [
                        'costCenterId' => $costCenter->id,
                        'psGroupId' => $psGroup->id,
                        'date_from' => request('date_from'),
                        'date_to' => request('date_to'),
                    ]) }}"
                        class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        PDF
                    </a>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode Material</th>
                            <th>Nama Material</th>
                            <th>Nama Karyawan</th>
                            <th class="text-right">Kg</th>
                            <th class="text-right">Lama Packing</th>
                            <th class="text-right">Productivity</th>
                            <th class="text-right">Harga/kg</th>
                            <th class="text-right">Rupiah</th>
                            <th>Yang Input</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($details as $detail)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($detail->tanggal)->translatedFormat('d M Y') }}</td>
                                <td>{{ $detail->material_code ?? '-' }}</td>
                                <td>{{ $detail->material_name }}</td>
                                <td>{{ $detail->employee_name }}</td>
                                <td class="text-right">{{ number_format($detail->total_kg, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($detail->lama_packing, 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($detail->productivity, 2, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($detail->harga_per_kg, 2, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp
                                        {{ number_format($detail->total_harga, 2, ',', '.') }}</strong></td>
                                <td>{{ $detail->user_name }}</td>

                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin-production.daily-activity.edit', $detail->id) }}"
                                            class="btn btn-warning btn-sm mr-2">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin-production.daily-activity.destroy', $detail->id) }}"
                                            method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
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
