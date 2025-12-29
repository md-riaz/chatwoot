<?php

namespace App\Services\Messages;

/**
 * Deprecated builder kept for compatibility only.
 * Use Actions (App\Actions\Message\CreateMessageAction) and
 * App\Actions\Message\SetInReplyToAction instead.
 */
class MessageBuilder
{
    public function __construct(...$args)
    {
        throw new \LogicException('MessageBuilder is deprecated. Use CreateMessageAction and related Actions instead.');
    }
}
