<?php

namespace App\Repositories\Filter;

use App\Models\Account;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class FilterRepository extends BaseRepository
{
    public function __construct()
    {
        // No specific model for this repository as it works with multiple models
    }

    /**
     * Get accessible inbox IDs for a user in an account
     */
    public function getAccessibleInboxIds(User $user, Account $account): Collection
    {
        // Check if user has admin role or account owner
        $accountUser = $user->accountUsers()
            ->where('account_id', $account->id)
            ->first();

        if (!$accountUser) {
            return collect();
        }

        // Admin users have access to all inboxes
        if ($accountUser->role === 'administrator') {
            return $account->inboxes()->pluck('id');
        }

        // Regular users only have access to assigned inboxes
        return $user->inboxes()
            ->where('account_id', $account->id)
            ->pluck('inboxes.id');
    }

    /**
     * Check if user should skip inbox filtering (admin users)
     */
    public function shouldSkipInboxFiltering(User $user, Account $account): bool
    {
        $accountUser = $user->accountUsers()
            ->where('account_id', $account->id)
            ->first();

        return $accountUser && $accountUser->role === 'administrator';
    }

    /**
     * Get user's team IDs for filtering
     */
    public function getUserTeamIds(User $user, Account $account): Collection
    {
        return $user->teams()
            ->where('account_id', $account->id)
            ->pluck('teams.id');
    }

    /**
     * Check if user can access specific inbox
     */
    public function canAccessInbox(User $user, int $inboxId, Account $account): bool
    {
        if ($this->shouldSkipInboxFiltering($user, $account)) {
            return true;
        }

        $accessibleInboxIds = $this->getAccessibleInboxIds($user, $account);
        return $accessibleInboxIds->contains($inboxId);
    }
}