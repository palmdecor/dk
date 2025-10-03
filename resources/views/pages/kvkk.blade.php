@extends('layouts.app')

@section('title', __('legal.kvkk_title'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-semibold text-primary mb-3">{{ __('legal.kvkk_heading') }}</h1>
                    <p class="text-muted">{{ __('legal.kvkk_intro') }}</p>
                    <ul class="list-group list-group-numbered mt-4">
                        <li class="list-group-item">{{ __('legal.kvkk_items.collect') }}</li>
                        <li class="list-group-item">{{ __('legal.kvkk_items.purpose') }}</li>
                        <li class="list-group-item">{{ __('legal.kvkk_items.rights') }}</li>
                        <li class="list-group-item">{{ __('legal.kvkk_items.contact') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
