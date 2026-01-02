<?php

namespace App\Traits;

use App\Enums\AccountUserRole;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;

trait HasAccountRoles
{
    /**
     * Scope to get users with administrator role
     */
    public function scopeAdministrators(Builder $query): Builder
    {
        return $query->wherePivot('role', AccountUserRole::ADMINISTRATOR->value);
    }

    /**
     * Scope to get users with agent role
     */
    public function scopeAgents(Builder $query): Builder
    {
        return $query->wherePivot('role', AccountUserRole::AGENT->value);
    }

    /**
     * Scope to exclude administrators
     */
    public function scopeNonAdministrators(Builder $query): Builder
    {
        return $query->where('role', '!=', AccountUserRole::ADMINISTRATOR->value);
    }

    /**
     * Check if user is administrator of given account
     */
    public function isAdministratorOf(Account $account): bool
    {
        return $this->accounts()
            ->wherePivot('role', AccountUserRole::ADMINISTRATOR->value)
            ->where('account_id', $account->id)
            ->exists();
    }

    /**
     * Check if user is agent of given account
     */
    public function isAgentOf(Account $account): bool
    {
        return $this->accounts()
            ->wherePivot('role', AccountUserRole::AGENT->value)
            ->where('account_id', $account->id)
            ->exists();
    }

    /**
     * Get all accounts where user is administrator
     */
    public function administratorAccounts()
    {
        return $this->accounts()->administrators();
    }

    /**
     * Get all accounts where user is agent
     */
    public function agentAccounts()
    {
        return $this->accounts()->agents();
    }
}