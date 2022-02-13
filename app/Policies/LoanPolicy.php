<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can apply loan.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function canApply(User $user)
    {
        return $user->hasRole('client');
    }

    /**
     * Determine whether the user can approve loan.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function canApprove(User $user)
    {
        return $user->hasRole('super-admin');
    }
}
