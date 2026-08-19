@extends('layouts.master')

@section('content')
    <div class="section-header">
        <h1>Update Password</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Account</div>
            <div class="breadcrumb-item">Update Password</div>
        </div>
    </div>

    <div class="section-body">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-header">
                        <h4>Form Update Password</h4>
                    </div>

                    <div class="card-body">

                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show">

                                {{ session('success') }}

                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>

                            </div>
                        @endif

                        <form action="{{ route('password.update') }}" method="POST">

                            @csrf
                            @method('PUT')

                            <div class="form-group mb-4">

                                <label for="current_password">
                                    Current Password
                                </label>

                                <input type="password" name="current_password" id="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror">

                                @error('current_password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="form-group mb-4">

                                <label for="password">
                                    New Password
                                </label>

                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror">

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="form-group mb-4">

                                <label for="password_confirmation">
                                    Password Confirmation
                                </label>

                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control @error('password_confirmation') is-invalid @enderror">

                                @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="form-group mb-0">

                                <button type="submit" class="btn btn-primary">
                                    Update Password
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
