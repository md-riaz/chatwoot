<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Determine if the user can view any conversations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->accounts()->where('account_id', $conversation->account_id)->exists();
    }

    /**
     * Determine if the user can create conversations.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the conversation.
     */
    public function update(User $user, Conversation $conversation): bool
    {
        return $user->accounts()->where('account_id', $conversation->account_id)->exists();
    }

    /**
     * Determine if the user can delete the conversation.
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        // Only admins can delete conversations
        return $user->accounts()
            ->wherePivot('role', 2)
            ->where('account_id', $conversation->account_id)
            ->exists();
    }

    /**
     * Determine if the user can assign the conversation.
     */
    public function assign(User $user, Conversation $conversation): bool
    {
        return $user->accounts()->where('account_id', $conversation->account_id)->exists();
    }
}
