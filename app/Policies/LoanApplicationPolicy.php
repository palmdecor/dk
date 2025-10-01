<?php

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;

class LoanApplicationPolicy
{
    public function view(User $user, LoanApplication $application): bool
    {
        return $user->id === $application->user_id || $user->can('manage-applications');
    }

    public function manage(User $user): bool
    {
        return $user->can('manage-applications');
    }
}
