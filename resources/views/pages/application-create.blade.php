@extends('layouts.app')

@section('title', __('application.create_title'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-semibold text-primary mb-4">{{ __('application.create_heading') }}</h1>

                    <form method="POST" action="{{ route('applications.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.national_id') }}</label>
                                <input type="text" name="national_id" value="{{ old('national_id') }}" class="form-control @error('national_id') is-invalid @enderror">
                                @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.monthly_income') }}</label>
                                <input type="number" name="monthly_income" value="{{ old('monthly_income') }}" class="form-control @error('monthly_income') is-invalid @enderror">
                                @error('monthly_income')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.employment_status') }}</label>
                                <input type="text" name="employment_status" value="{{ old('employment_status') }}" class="form-control @error('employment_status') is-invalid @enderror">
                                @error('employment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.loan_amount') }}</label>
                                <input type="number" name="loan_amount" value="{{ old('loan_amount', 100000) }}" class="form-control @error('loan_amount') is-invalid @enderror">
                                @error('loan_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.loan_term') }}</label>
                                <input type="number" name="loan_term" value="{{ old('loan_term', 36) }}" class="form-control @error('loan_term') is-invalid @enderror">
                                @error('loan_term')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('application.form.email') }}</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('application.form.notes') }}</label>
                                <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 form-check">
                                <input class="form-check-input @error('kvkk_approved') is-invalid @enderror" type="checkbox" value="1" id="kvkkApproval" name="kvkk_approved" {{ old('kvkk_approved') ? 'checked' : '' }}>
                                <label class="form-check-label" for="kvkkApproval">{!! __('application.form.kvkk_text', ['link' => route('kvkk')]) !!}</label>
                                @error('kvkk_approved')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-primary btn-lg">{{ __('application.form.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
