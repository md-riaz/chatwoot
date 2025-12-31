<?php

namespace App\Services\Channels;

use App\Models\Message;

abstract class BaseSendOnChannelService
{
    public function __construct(protected Message $message)
    {
    }

    public function perform(): void
    {
        if (! $this->validateTargetChannel()) {
            return;
        }

        if (! $this->outgoingMessage()) {
            return;
        }

        if ($this->invalidMessage()) {
            return;
        }

        $this->performReply();
    }

    abstract protected function channelClass(): string;

    abstract protected function performReply(): void;

    protected function conversation()
    {
        return $this->message->conversation;
    }

    protected function inbox()
    {
        return $this->conversation()->inbox;
    }

    protected function channel()
    {
        return $this->inbox()->channel;
    }

    protected function outgoingMessage(): bool
    {
        return in_array($this->message->message_type, [Message::TYPE_OUTGOING, Message::TYPE_TEMPLATE], true) || ($this->message->message_type === Message::TYPE_OUTGOING);
    }

    protected function invalidMessage(): bool
    {
        if ($this->message->private) {
            return true;
        }

        // outgoing message originated from channel (source_id present) - avoid loops
        return ! empty($this->message->source_id);
    }

    protected function validateTargetChannel(): bool
    {
        $channel = $this->channel();
        if (! $channel) {
            return false;
        }

        $expected = $this->channelClass();
        return $channel::class === $expected || is_a($channel, $expected);
    }
}
