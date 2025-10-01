<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $minAmount = (float) config('loan.min_amount');
        $maxAmount = (float) config('loan.max_amount');

        return [
            'national_id' => ['required', 'digits:11'],
            'monthly_income' => ['required', 'numeric', 'min:0'],
            'employment_status' => ['required', 'string', 'max:255'],
            'loan_amount' => ['required', 'numeric', 'min:'.$minAmount, 'max:'.$maxAmount],
            'loan_term' => ['required', 'integer', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'notes' => ['nullable', 'string', 'max:500'],
            'kvkk_approved' => ['accepted'],
        ];
    }
}
