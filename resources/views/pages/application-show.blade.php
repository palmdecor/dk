@extends('layouts.app')

@section('title', __('application.detail_title', ['id' => $application->id]))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h4 fw-semibold text-primary">{{ __('application.detail_heading', ['id' => $application->id]) }}</h1>
                            <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning') }}">{{ __('application.status_'.$application->status) }}</span>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">{{ __('application.back_dashboard') }}</a>
                    </div>

                    <dl class="row g-3">
                        <dt class="col-sm-4 text-muted">{{ __('application.form.loan_amount') }}</dt>
                        <dd class="col-sm-8 fw-semibold">{{ format_currency($application->loan_amount) }}</dd>

                        <dt class="col-sm-4 text-muted">{{ __('application.form.loan_term') }}</dt>
                        <dd class="col-sm-8">{{ $application->loan_term }} {{ __('dashboard.table.months') }}</dd>

                        <dt class="col-sm-4 text-muted">{{ __('application.form.monthly_income') }}</dt>
                        <dd class="col-sm-8">{{ format_currency($application->monthly_income) }}</dd>

                        <dt class="col-sm-4 text-muted">{{ __('application.form.employment_status') }}</dt>
                        <dd class="col-sm-8">{{ $application->employment_status }}</dd>

                        <dt class="col-sm-4 text-muted">{{ __('application.form.phone') }}</dt>
                        <dd class="col-sm-8">{{ $application->phone }}</dd>

                        <dt class="col-sm-4 text-muted">{{ __('application.form.email') }}</dt>
                        <dd class="col-sm-8">{{ $application->email }}</dd>

                        <dt class="col-sm-4 text-muted">{{ __('application.form.notes') }}</dt>
                        <dd class="col-sm-8">{{ $application->notes ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
