<?php

namespace App\Actions\Message;

use App\Models\Message;
use App\Repositories\Message\MessageRepository;
use Illuminate\Support\Facades\Log;
use Lorisleiva\LaravelActions\Concerns\AsAction;

class SetInReplyToAction
{
    use AsAction;

    public function __construct(private MessageRepository $messageRepository)
    {
    }

    public function handle(Message $message, $inReplyTo = null, $inReplyToExternalId = null): Message
    {
        if (empty($inReplyTo) && empty($inReplyToExternalId)) {
            return $message;
        }

        $conversation = $message->conversation;
        $inReplyMsg = null;

        if (! empty($inReplyTo)) {
            $inReplyMsg = $conversation->messages()->find($inReplyTo);
        } elseif (! empty($inReplyToExternalId)) {
            $inReplyMsg = $conversation->messages()->where('source_id', $inReplyToExternalId)->first();
        }

        if ($inReplyMsg) {
            $attrs = $message->content_attributes ?? [];
            $attrs['in_reply_to_external_id'] = $inReplyMsg->source_id ?? null;
            $attrs['in_reply_to'] = $inReplyMsg->id;
            $message->content_attributes = $attrs;
            try {
                $this->messageRepository->update($message->id, ['content_attributes' => $message->content_attributes]);
                $message->refresh();
            } catch (\Exception $e) {
                Log::warning('Failed to set in-reply-to on message', ['error' => $e->getMessage()]);
            }
        }

        return $message;
    }
}
