<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        return $user->accounts()->where('account_id', $message->account_id)->exists();
    }

    /**
     * Determine if the user can create messages.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the message.
     */
    public function update(User $user, Message $message): bool
    {
        // Only the sender can update their own message
        return $message->sender_type === 'App\Models\User' && $message->sender_id === $user->id;
    }

    /**
     * Determine if the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        // Only the sender can delete their own message, or admins
        if ($message->sender_type === 'App\Models\User' && $message->sender_id === $user->id) {
            return true;
        }

        return $user->accounts()
            ->wherePivot('role', 1) // 1 = administrator
            ->where('account_id', $message->account_id)
            ->exists();
    }
}
