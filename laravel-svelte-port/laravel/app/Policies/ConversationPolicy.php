<?php

namespace App\Policies;

use App\Models\AccountUser;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Determine if the user can view any conversations.
     */
    public function viewAny(User $user, $accountId): bool
    {
        $accountUser = $this->getAccountUser($user, $accountId);
        if (!$accountUser) {
            return false;
        }

        return $accountUser->hasPermission('conversation_manage') ||
               $accountUser->hasPermission('conversation_unassigned_manage') ||
               $accountUser->hasPermission('conversation_participating_manage');
    }

    /**
     * Determine if the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        $accountUser = $this->getAccountUser($user, $conversation->account_id);
        if (!$accountUser) {
            return false;
        }

        // Can manage all conversations
        if ($accountUser->hasPermission('conversation_manage')) {
            return true;
        }

        // Can manage unassigned conversations
        if ($accountUser->hasPermission('conversation_unassigned_manage') && !$conversation->assignee_id) {
            return true;
        }

        // Can manage participating conversations
        if ($accountUser->hasPermission('conversation_participating_manage')) {
            return $this->isParticipating($user, $conversation);
        }

        return false;
    }

    /**
     * Determine if the user can create conversations.
     */
    public function create(User $user, $accountId): bool
    {
        $accountUser = $this->getAccountUser($user, $accountId);
        if (!$accountUser) {
            return false;
        }

        return $accountUser->hasPermission('conversation_manage') ||
               $accountUser->hasPermission('conversation_unassigned_manage');
    }

    /**
     * Determine if the user can update the conversation.
     */
    public function update(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the user can delete the conversation.
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        $accountUser = $this->getAccountUser($user, $conversation->account_id);
        if (!$accountUser) {
            return false;
        }

        // Only users with full conversation management can delete
        return $accountUser->hasPermission('conversation_manage');
    }

    /**
     * Determine if the user can assign the conversation.
     */
    public function assign(User $user, Conversation $conversation): bool
    {
        $accountUser = $this->getAccountUser($user, $conversation->account_id);
        if (!$accountUser) {
            return false;
        }

        return $accountUser->hasPermission('conversation_manage') ||
               $accountUser->hasPermission('conversation_unassigned_manage');
    }

    /**
     * Get the account user relationship
     */
    private function getAccountUser(User $user, int $accountId): ?AccountUser
    {
        return $user->accountUsers()->where('account_id', $accountId)->first();
    }

    /**
     * Check if the user is participating in the conversation
     */
    private function isParticipating(User $user, Conversation $conversation): bool
    {
        // User is assigned to the conversation
        if ($conversation->assignee_id === $user->id) {
            return true;
        }

        // User is a participant in the conversation
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }
}