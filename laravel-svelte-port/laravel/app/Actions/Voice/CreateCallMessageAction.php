<?php

namespace App\Actions\Voice;

use App\Models\Message;
use App\Models\Conversation;
use App\Repositories\Message\MessageRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCallMessageAction
{
    use AsAction;

    public function __construct(private MessageRepository $messageRepository)
    {
    }

    /**
     * Create or update an activity message representing a voice call.
     *
     * @param Conversation $conversation
     * @param string $direction
     * @param array $payload
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @param array $timestamps
     * @return Message
     */
    public function handle(Conversation $conversation, string $direction, array $payload = [], $user = null, array $timestamps = []): Message
    {
        // Check for existing voice call message (Rails pattern: update instead of create duplicate)
        $existingMessage = $conversation->messages()
            ->where('content_type', Message::CONTENT_VOICE_CALL)
            ->orderByDesc('created_at')
            ->first();

        if ($existingMessage) {
            return $this->updateMessage($existingMessage, $direction, $payload, $user);
        }

        return $this->createMessage($conversation, $direction, $payload, $user, $timestamps);
    }

    private function updateMessage(Message $message, string $direction, array $payload, $user = null): Message
    {
        $data = [
            'message_type' => $direction === 'outbound' ? Message::TYPE_OUTGOING : Message::TYPE_INCOMING,
            'content_attributes' => ['data' => $this->buildPayload($direction, $payload)],
        ];

        if ($user) {
            $data['sender_id'] = $user->id;
            $data['sender_type'] = get_class($user);
        }

        $message->update($data);

        // Dispatch updated event if available
        try {
            event(new \App\Events\Message\MessageUpdated($message));
        } catch (\Throwable $e) {
            // swallow
        }

        return $message;
    }

    private function createMessage(Conversation $conversation, string $direction, array $payload, $user = null, array $timestamps = []): Message
    {
        $data = [
            'account_id' => $conversation->account_id,
            'conversation_id' => $conversation->id,
            'inbox_id' => $conversation->inbox_id,
            'message_type' => $direction === 'outbound' ? Message::TYPE_OUTGOING : Message::TYPE_INCOMING,
            'content_type' => Message::CONTENT_VOICE_CALL,
            'content' => 'Voice Call',
            'content_attributes' => ['data' => $this->buildPayload($direction, $payload)],
            'private' => false,
        ];

        if ($user) {
            $data['sender_id'] = $user->id;
            $data['sender_type'] = get_class($user);
        }

        if (!empty($timestamps['created_at'])) {
            $data['created_at'] = $timestamps['created_at'];
        }

        $message = $this->messageRepository->create($data);

        // Dispatch created event if available
        try {
            event(new \App\Events\Message\MessageCreated($message));
        } catch (\Throwable $e) {
            // swallow
        }

        return $message;
    }

    private function buildPayload(string $direction, array $payload): array
    {
        $data = collect($payload)->only([
            'call_sid',
            'status',
            'call_direction',
            'conference_sid',
            'from_number',
            'to_number'
        ])->toArray();

        $data['call_direction'] = $direction;
        
        if (!empty($payload['meta'])) {
            $data['meta'] = $payload['meta'];
        }

        return $data;
    }
}
