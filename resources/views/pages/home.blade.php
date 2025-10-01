@extends('layouts.app')

@section('title', __('home.meta_title'))

@section('content')
<div class="container">
    <div class="row align-items-center py-5">
        <div class="col-lg-6">
            <h1 class="display-5 fw-bold text-primary mb-3">{{ __('home.hero_title') }}</h1>
            <p class="lead text-muted">{{ __('home.hero_subtitle') }}</p>
            <a href="{{ route('applications.create') }}" class="btn btn-primary btn-lg mt-3">{{ __('home.hero_cta') }}</a>
        </div>
        <div class="col-lg-6">
            @include('pages.partials.calculator')
        </div>
    </div>

    <section class="py-5">
        <h2 class="h3 text-center fw-semibold mb-4">{{ __('home.advantages_title') }}</h2>
        <div class="row g-4">
            @foreach($advantages as $advantage)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 text-center p-3">
                        <div class="card-body">
                            <span class="display-6 text-primary">★</span>
                            <h3 class="h6 mt-3">{{ $advantage }}</h3>
                            <p class="text-muted">{{ __('home.advantages_description') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="py-5 bg-white rounded-4 shadow-sm">
        <div class="container">
            <h2 class="h3 text-center fw-semibold mb-4">{{ __('home.testimonials_title') }}</h2>
            <div class="row g-4">
                @foreach($testimonials as $testimonial)
                    <div class="col-md-4">
                        <div class="card border-0 h-100">
                            <div class="card-body">
                                <p class="text-muted">“{{ $testimonial['content'] }}”</p>
                                <p class="fw-semibold mb-0">{{ $testimonial['name'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const amountInput = document.getElementById('calculatorAmount');
        const termInput = document.getElementById('calculatorTerm');
        const rateInput = document.getElementById('calculatorRate');
        const resultContainer = document.getElementById('calculatorResult');

        function calculate() {
            const amount = parseFloat(amountInput.value || 0);
            const term = parseInt(termInput.value || 0);
            const rate = parseFloat(rateInput.value || {{ config('loan.default_interest_rate') }}) / 100 / 12;

            if (!amount || !term) {
                resultContainer.innerHTML = '';
                return;
            }

            const payment = rate > 0 ? (amount * rate) / (1 - Math.pow(1 + rate, -term)) : amount / term;

            resultContainer.innerHTML = `
                <div class="alert alert-info mt-3">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold">{{ __('home.calculator_monthly_payment') }}: ${payment.toFixed(2)} ₺</span>
                        <span>{{ __('home.calculator_summary') }}</span>
                    </div>
                </div>
            `;
        }

        [amountInput, termInput, rateInput].forEach(input => {
            input.addEventListener('input', calculate);
        });

        calculate();
    });
</script>
@endpush
