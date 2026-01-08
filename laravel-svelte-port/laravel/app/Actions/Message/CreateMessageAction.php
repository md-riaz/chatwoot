<?php

namespace App\Actions\Message;

use App\Data\Message\MessageData;
use App\Events\Message\MessageCreated;
use App\Models\Message;
use App\Repositories\Conversation\ConversationRepository;
use App\Repositories\Message\MessageRepository;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMessageAction
{
    use AsAction;

    public function __construct(
        private MessageRepository $messageRepository,
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(MessageData $data): Message
    {
        return DB::transaction(function () use ($data) {
            $message = $this->messageRepository->create($data->toArray());

            // Handle in-reply-to references via Action to follow project pattern
            try {
                $inReplyTo = $message->content_attributes['in_reply_to'] ?? null;
                $inReplyToExternal = $message->content_attributes['in_reply_to_external_id'] ?? null;
                if ($inReplyTo || $inReplyToExternal) {
                    \App\Actions\Message\SetInReplyToAction::run($message, $inReplyTo, $inReplyToExternal);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('InReplyTo processing failed', ['error' => $e->getMessage()]);
            }

            // Update conversation last activity
            $this->conversationRepository->update($data->conversation_id, [
                'last_activity_at' => now(),
            ]);

            // If this is the first agent reply, update first_reply_created_at
            if ($data->message_type === Message::TYPE_OUTGOING && ! $data->private) {
                $conversation = $this->conversationRepository->find($data->conversation_id);
                if (! $conversation->first_reply_created_at) {
                    $this->conversationRepository->update($data->conversation_id, [
                        'first_reply_created_at' => now(),
                    ]);
                }
            }

            // Trigger event
            event(new MessageCreated($message));

            return $message;
        });
    }

    public function asJob(MessageData $data): void
    {
        $this->handle($data);
    }

    public function rules(): array
    {
        return MessageData::rules();
    }
}
