<?php

namespace App\Policies;

use App\Models\Inbox;
use App\Models\User;

class InboxPolicy
{
    /**
     * Determine if the user can view any inboxes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the inbox.
     */
    public function view(User $user, Inbox $inbox): bool
    {
        return $user->accounts()->where('account_id', $inbox->account_id)->exists();
    }

    /**
     * Determine if the user can create inboxes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the inbox.
     */
    public function update(User $user, Inbox $inbox): bool
    {
        // Only admins can update inboxes
        return $user->accounts()
            ->wherePivot('role', 1) // 1 = administrator
            ->where('account_id', $inbox->account_id)
            ->exists();
    }

    /**
     * Determine if the user can delete the inbox.
     */
    public function delete(User $user, Inbox $inbox): bool
    {
        // Only admins can delete inboxes
        return $user->accounts()
            ->wherePivot('role', 1) // 1 = administrator
            ->where('account_id', $inbox->account_id)
            ->exists();
    }
}
