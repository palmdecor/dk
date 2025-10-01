@extends('layouts.app')

@section('title', __('auth.login_title'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-semibold text-primary mb-4">{{ __('auth.login_heading') }}</h1>
                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('auth.form.email') }}</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('auth.form.password') }}</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">{{ __('auth.form.remember') }}</label>
                        </div>
                        <button class="btn btn-primary w-100">{{ __('auth.login_button') }}</button>
                    </form>
                    <p class="text-muted mt-3 mb-0">{{ __('auth.no_account') }} <a href="{{ route('register') }}">{{ __('auth.register_link') }}</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
