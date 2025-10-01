<?php

namespace Database\Factories;

use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanApplicationFactory extends Factory
{
    protected $model = LoanApplication::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'national_id' => $this->faker->numerify('###########'),
            'monthly_income' => $this->faker->numberBetween(10000, 50000),
            'employment_status' => $this->faker->randomElement(['Çalışıyor', 'Serbest Meslek', 'Emekli']),
            'loan_amount' => $this->faker->numberBetween(50000, 250000),
            'loan_term' => $this->faker->numberBetween(12, 60),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'notes' => $this->faker->sentence(10),
            'status' => LoanApplication::STATUS_PENDING,
            'kvkk_approved' => true,
        ];
    }
}
