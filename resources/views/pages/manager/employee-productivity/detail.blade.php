@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Produktivitas Karyawan</h1>
    </div>

    <div class="section-body">

        {{-- FILTER TANGGAL --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET" action="{{ route('manager.employee-productivity.detail', $employee->id) }}">

                    <div class="row">

                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <label>Dari Tanggal</label>
                                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                            </div>
                        </div>

                    </div>

                    <div>

                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('manager.employee-productivity.detail', $employee->id) }}"
                            class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="text-muted mb-2">
                            Nama Karyawan
                        </div>
                        <h4 class="mb-0">
                            {{ $employee->name }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-center">Total KG</th>
                            <th class="text-center">Total Rupiah</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($allDetails as $item)
                            <tr>

                                <td>{{ \Carbon\Carbon::parse($item['tanggal'])->format('d-m-Y') }}</td>

                                <td>{{ $item['product'] }}</td>

                                <td class="text-center">
                                    {{ number_format($item['total_kg'], 2) }}
                                </td>

                                <td class="text-center">
                                    Rp {{ number_format($item['total_harga'], 0, ',', '.') }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center">
                                    Belum ada data produk yang dikerjakan
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                    @if (count($allDetails) > 0)
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Grand Total</th>
                                <th class="text-center">
                                    {{ number_format(collect($allDetails)->sum('total_kg'), 2) }}
                                </th>
                                <th class="text-center">
                                    Rp {{ number_format(collect($allDetails)->sum('total_harga'), 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    @endif

                </table>

            </div>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-2">
            Kembali
        </a>

    </div>
@endsection
