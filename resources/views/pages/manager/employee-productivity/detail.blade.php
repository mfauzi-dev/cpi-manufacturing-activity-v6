@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Produktivitas Karyawan</h1>
    </div>

    <div class="section-body">

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

                    <div class="mt-2">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filter
                        </button>

                        <a href="{{ route('manager.employee-productivity.detail', $employee->id) }}"
                            class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i>
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        <div class="row mb-2">

            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="text-muted mb-2">
                            Department
                        </div>

                        <h4 class="mb-0">
                            {{ $employee->department->name ?? '-' }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="text-muted mb-2">
                            Cost Center
                        </div>

                        <h4 class="mb-0">
                            {{ $employee->costCenter->code ?? '-' }}
                            -
                            {{ $employee->costCenter->name ?? '-' }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
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

        <div class="card">

            <div class="card-body table-responsive">

                <div class="mb-4">

                    <a href="{{ route('manager.employee-productivity.export-excel', [
                        'search' => $employee->name,
                        'employee_id' => $employee->id,
                        'from' => request('from'),
                        'to' => request('to'),
                    ]) }}"
                        class="btn btn-success">

                        <i class="fas fa-file-excel"></i>
                        Excel

                    </a>

                    <a href="{{ route('manager.employee-productivity.export-pdf', [
                        'search' => $employee->name,
                        'employee_id' => $employee->id,
                        'from' => request('from'),
                        'to' => request('to'),
                    ]) }}"
                        class="btn btn-danger" target="_blank">

                        <i class="fas fa-file-pdf"></i>
                        PDF

                    </a>

                </div>

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-center">
                                Productivity
                            </th>
                            <th class="text-center">
                                Total KG
                            </th>

                            @if (strtolower(trim($employee->department->name ?? '')) !== 'further processing')
                                <th class="text-center">
                                    Total Rupiah
                                </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($allDetails as $item)
                            <tr>

                                <td>
                                    {{ \Carbon\Carbon::parse($item->activity_date)->format('d-m-Y') }}
                                </td>

                                <td>
                                    {{ $item->product->material_name ?? '-' }}
                                </td>

                                <td class="text-center">

                                    @if (strtolower(trim($employee->department->name ?? '')) === 'slaughter house')
                                        {{ $item->display_productivity_actual !== null
                                            ? number_format($item->display_productivity_actual, 2, ',', '.')
                                            : '-' }}
                                    @else
                                        {{ $item->display_productivity !== null ? number_format($item->display_productivity, 2, ',', '.') : '-' }}
                                    @endif

                                </td>

                                <td class="text-center">
                                    {{ number_format($item->total_kg ?? 0, 2, ',', '.') }}
                                </td>

                                @if (strtolower(trim($employee->department->name ?? '')) !== 'further processing')
                                    <td class="text-right">

                                        @if ($item->display_total_harga !== null)
                                            Rp {{ number_format($item->display_total_harga, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif

                                    </td>
                                @endif

                            </tr>

                        @empty

                            <tr>

                                <td colspan="{{ strtolower(trim($employee->department->name ?? '')) === 'further processing' ? 4 : 5 }}"
                                    class="text-center">

                                    Belum ada data produk yang dikerjakan

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                    @if ($allDetails->count() > 0)
                        <tfoot>

                            <tr>

                                <th colspan="2" class="text-right">
                                    Grand Total
                                </th>

                                <th class="text-center">
                                    -
                                </th>

                                <th class="text-center">
                                    {{ number_format($allDetails->sum('total_kg'), 2, ',', '.') }}
                                </th>

                                @if (strtolower(trim($employee->department->name ?? '')) !== 'further processing')
                                    <th class="text-right">
                                        Rp
                                        {{ number_format(
                                            $allDetails->sum(function ($item) {
                                                return $item->display_total_harga ?? 0;
                                            }),
                                            0,
                                            ',',
                                            '.',
                                        ) }}
                                    </th>
                                @endif

                            </tr>

                        </tfoot>
                    @endif

                </table>

            </div>

        </div>

        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-2">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>

    </div>
@endsection
