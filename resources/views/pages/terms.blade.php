@extends('layouts.app')

@section('title', __('legal.terms_title'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-semibold text-primary mb-3">{{ __('legal.terms_heading') }}</h1>
                    <p class="text-muted">{{ __('legal.terms_intro') }}</p>
                    <ol class="mt-4">
                        <li class="mb-2">{{ __('legal.terms_items.service') }}</li>
                        <li class="mb-2">{{ __('legal.terms_items.liability') }}</li>
                        <li class="mb-2">{{ __('legal.terms_items.privacy') }}</li>
                        <li class="mb-2">{{ __('legal.terms_items.changes') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
