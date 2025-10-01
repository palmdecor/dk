<div class="card border-0 shadow-lg">
    <div class="card-body p-4">
        <h2 class="h5 fw-semibold mb-3 text-primary">{{ __('home.calculator_title') }}</h2>
        <form method="POST" action="{{ route('calculator.calculate') }}">
            @csrf
            <div class="mb-3">
                <label for="calculatorAmount" class="form-label">{{ __('home.calculator_amount') }}</label>
                <input type="number" min="{{ config('loan.min_amount') }}" max="{{ config('loan.max_amount') }}" step="1000" class="form-control" id="calculatorAmount" name="amount" value="{{ old('amount', 100000) }}">
            </div>
            <div class="mb-3">
                <label for="calculatorTerm" class="form-label">{{ __('home.calculator_term') }}</label>
                <input type="number" min="3" max="120" step="3" class="form-control" id="calculatorTerm" name="term_months" value="{{ old('term_months', 36) }}">
            </div>
            <div class="mb-3">
                <label for="calculatorRate" class="form-label">{{ __('home.calculator_rate') }}</label>
                <input type="number" step="0.01" min="0" class="form-control" id="calculatorRate" name="interest_rate" value="{{ old('interest_rate', config('loan.default_interest_rate')) }}">
            </div>
            <button type="submit" class="btn btn-primary w-100">{{ __('home.calculator_button') }}</button>
        </form>
        <div id="calculatorResult">
            @if(session('calculation'))
                <x-alert type="info" class="mt-3">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold">{{ __('home.calculator_monthly_payment') }}: {{ format_currency(session('calculation.monthly_payment')) }}</span>
                        <small class="text-muted">{{ __('home.calculator_result_note', ['term' => session('calculation.term'), 'rate' => session('calculation.rate')]) }}</small>
                    </div>
                </x-alert>
            @endif
        </div>
    </div>
</div>
