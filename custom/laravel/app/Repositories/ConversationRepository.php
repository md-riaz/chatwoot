<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\User;

class ConversationRepository
{
    /**
     * Assign the conversation to a user (agent).
     * This is a minimal implementation: set `assignee_id` and save.
     *
     * @param Conversation $conversation
     * @param User $agent
     * @return Conversation
     */
    public function assignToAgent(Conversation $conversation, User $agent): Conversation
    {
        // Delegate to the canonical conversation repository implementation.
        $repo = app(\App\Repositories\Conversation\ConversationRepository::class);

        $repo->update($conversation->id, [
            'assignee_id' => $agent->id,
        ]);

        return $conversation->fresh();
    }
}
