@extends('layouts.app')

@section('title', __('contact.title'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-semibold text-primary mb-3">{{ __('contact.heading') }}</h1>
                    <p class="text-muted">{{ __('contact.description') }}</p>

                    @if(session('status'))
                        <x-alert type="success">{{ session('status') }}</x-alert>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="mt-4">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('contact.form.name') }}</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('contact.form.email') }}</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('contact.form.phone') }}</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('contact.form.message') }}</label>
                                <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-primary">{{ __('contact.form.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
