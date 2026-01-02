<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Service for filtering search results based on user permissions.
 * Ensures users can only access data from inboxes they are assigned to.
 */
class PermissionFilterService
{
    /**
     * Filter conversations based on user's inbox access.
     */
    public function filterConversations(Builder $query, User $user, Account $account): Builder
    {
        $accessibleInboxIds = $this->getAccessibleInboxIds($user, $account);
        
        if ($accessibleInboxIds->isEmpty()) {
            // If user has no inbox access, return empty result
            return $query->whereRaw('1 = 0');
        }
        
        return $query->whereIn('inbox_id', $accessibleInboxIds);
    }

    /**
     * Filter messages based on user's inbox access through conversations.
     */
    public function filterMessages(Builder $query, User $user, Account $account): Builder
    {
        $accessibleInboxIds = $this->getAccessibleInboxIds($user, $account);
        
        if ($accessibleInboxIds->isEmpty()) {
            // If user has no inbox access, return empty result
            return $query->whereRaw('1 = 0');
        }
        
        return $query->whereHas('conversation', function ($conversationQuery) use ($accessibleInboxIds) {
            $conversationQuery->whereIn('inbox_id', $accessibleInboxIds);
        });
    }

    /**
     * Filter contacts based on user's inbox access through contact inboxes.
     */
    public function filterContacts(Builder $query, User $user, Account $account): Builder
    {
        $accessibleInboxIds = $this->getAccessibleInboxIds($user, $account);
        
        if ($accessibleInboxIds->isEmpty()) {
            // If user has no inbox access, return empty result
            return $query->whereRaw('1 = 0');
        }
        
        return $query->whereHas('contactInboxes', function ($contactInboxQuery) use ($accessibleInboxIds) {
            $contactInboxQuery->whereIn('inbox_id', $accessibleInboxIds);
        });
    }

    /**
     * Get the inbox IDs that the user has access to within the account.
     */
    public function getAccessibleInboxIds(User $user, Account $account): Collection
    {
        // Check if user is a super admin or account admin
        if ($this->isAccountAdmin($user, $account)) {
            // Account admins have access to all inboxes in the account
            return $account->inboxes()->pluck('id');
        }
        
        // Regular users only have access to inboxes they are members of
        return $user->assignedInboxes()
            ->where('account_id', $account->id)
            ->pluck('inboxes.id');
    }

    /**
     * Check if user is an admin for the given account.
     */
    private function isAccountAdmin(User $user, Account $account): bool
    {
        // Check if user has admin role in this account
        $accountUser = $user->accountUsers()
            ->where('account_id', $account->id)
            ->first();
            
        return $accountUser && $accountUser->role === 'administrator';
    }

    /**
     * Check if inbox filtering should be skipped for the user.
     * This is used for super admins or when explicitly configured.
     */
    public function shouldSkipInboxFiltering(User $user, Account $account): bool
    {
        // For now, only skip for account administrators
        // In the future, this could be extended for super admin users
        return $this->isAccountAdmin($user, $account);
    }
}