<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanCalculatorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $testimonials = [
            [
                'name' => 'Ayşe Yılmaz',
                'content' => 'Hızlı başvuru ve anında geri dönüş sayesinde ihtiyacım olan krediye kolayca ulaştım.',
            ],
            [
                'name' => 'Mehmet Kaya',
                'content' => 'Güvenilir arayüz ve detaylı kredi teklifleri ile çok memnun kaldım.',
            ],
            [
                'name' => 'Elif Demir',
                'content' => 'Müşteri temsilcileri çok ilgiliydi, süreç boyunca sürekli bilgilendirildim.',
            ],
        ];

        $advantages = [
            'Esnek ödeme planları',
            '7/24 başvuru imkanı',
            'Şeffaf faiz oranları',
            'Uzman finans danışmanlığı',
        ];

        return view('pages.home', compact('testimonials', 'advantages'));
    }

    public function calculate(LoanCalculatorRequest $request): RedirectResponse
    {
        $monthlyRate = $request->interestRate() / 12;
        $installments = $request->input('term_months');
        $amount = $request->input('amount');

        $monthlyPayment = $monthlyRate > 0
            ? ($amount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$installments))
            : $amount / $installments;

        return back()->with('calculation', [
            'monthly_payment' => $monthlyPayment,
            'amount' => $amount,
            'term' => $installments,
            'rate' => $request->interestRate() * 100,
        ]);
    }
}
