<?php

namespace App\Services\Conversations;

/**
 * Deprecated builder kept for compatibility only.
 * Use Actions (App\Actions\Conversation\CreateConversationAction) instead.
 */
class ConversationBuilder
{
    public function __construct(...$args)
    {
        throw new \LogicException('ConversationBuilder is deprecated. Use CreateConversationAction instead.');
    }
}
