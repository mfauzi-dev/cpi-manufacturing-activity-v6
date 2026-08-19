@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Position</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Position</a></div>
            <div class="breadcrumb-item"><a href="#">Table</a></div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('danger') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('manager.position.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-lg col-md-4 mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama posisi.."
                            value="{{ $search ?? '' }}">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('manager.position.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Daftar Position</h4>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th witdth="60">No.</th>
                            <th>Name</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($positions as $index => $position)
                            <tr>
                                <td>{{ $positions->firstItem() + $index }}</td>
                                <td>{{ $position->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    Daftar Position belum ada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-right">
            {{ $positions->withQueryString()->links() }}
        </div>
    </div>
@endsection
