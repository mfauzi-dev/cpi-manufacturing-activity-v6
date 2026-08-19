@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>PS Group</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">PS Group</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('danger'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('danger') }}

            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form action="{{ route('manager.ps-group.index') }}" method="GET">
                <div class="row">

                    <div class="col-lg-6 col-md-6 mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama PS Group..."
                            value="{{ $search }}">
                    </div>

                    <div class="col-lg-6 col-md-6 mb-3">
                        <select name="cost_center_id" class="form-control">
                            <option value="">-- Semua Cost Center --</option>

                            @foreach ($costCenterList as $costCenter)
                                <option value="{{ $costCenter->id }}"
                                    {{ $costCenterId == $costCenter->id ? 'selected' : '' }}>
                                    {{ $costCenter->code }} - {{ $costCenter->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('manager.ps-group.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Daftar PS Group</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="60">No.</th>
                            <th>Cost Center</th>
                            <th>PS Group</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($psGroups as $index => $psGroup)
                            <tr>
                                <td>{{ $psGroups->firstItem() + $index }}</td>

                                <td>
                                    {{ $psGroup->costCenter->code }}
                                    -
                                    {{ $psGroup->costCenter->name }}
                                </td>

                                <td>{{ $psGroup->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    Data PS Group belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        <div class="card-footer text-right">
            {{ $psGroups->withQueryString()->links() }}
        </div>
    </div>
@endsection
