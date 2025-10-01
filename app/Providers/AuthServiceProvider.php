<?php

namespace App\Providers;

use App\Models\LoanApplication;
use App\Models\User;
use App\Policies\LoanApplicationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        LoanApplication::class => LoanApplicationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-applications', fn (User $user) => $user->hasRole('admin'));
    }
}
