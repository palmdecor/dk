<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minAmount = (float) config('loan.min_amount');
        $maxAmount = (float) config('loan.max_amount');

        return [
            'amount' => ['required', 'numeric', 'min:'.$minAmount, 'max:'.$maxAmount],
            'term_months' => ['required', 'integer', 'min:3', 'max:120'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function interestRate(): float
    {
        $rate = $this->input('interest_rate', config('loan.default_interest_rate'));

        return (float) $rate / 100;
    }
}
