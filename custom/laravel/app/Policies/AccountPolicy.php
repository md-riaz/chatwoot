<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    /**
     * Determine if the user can view any accounts.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can see their own accounts
    }

    /**
     * Determine if the user can view the account.
     */
    public function view(User $user, Account $account): bool
    {
        return $user->accounts()->where('account_id', $account->id)->exists();
    }

    /**
     * Determine if the user can create accounts.
     */
    public function create(User $user): bool
    {
        // Only super admins can create accounts
        // For now, allow all authenticated users
        return true;
    }

    /**
     * Determine if the user can update the account.
     */
    public function update(User $user, Account $account): bool
    {
        // Must be an admin of the account
        return $user->accounts()
            ->wherePivot('role', 2) // Admin role
            ->where('account_id', $account->id)
            ->exists();
    }

    /**
     * Determine if the user can delete the account.
     */
    public function delete(User $user, Account $account): bool
    {
        // Must be an admin of the account
        return $user->accounts()
            ->wherePivot('role', 2) // Admin role
            ->where('account_id', $account->id)
            ->exists();
    }
}
