<?php

namespace App\Services\Messages;

/**
 * Deprecated builder kept for compatibility only.
 * Use App\Actions\Message\SetInReplyToAction instead.
 */
class InReplyToMessageBuilder
{
    public function __construct(...$args)
    {
        throw new \LogicException('InReplyToMessageBuilder is deprecated. Use SetInReplyToAction instead.');
    }
}
