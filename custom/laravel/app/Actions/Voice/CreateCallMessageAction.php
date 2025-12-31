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
     * Create an activity message representing a voice call.
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
        $data = [
            'account_id' => $conversation->account_id,
            'conversation_id' => $conversation->id,
            'inbox_id' => $conversation->inbox_id,
            'message_type' => Message::TYPE_ACTIVITY,
            'content_type' => Message::CONTENT_VOICE_CALL,
            'content' => json_encode(array_merge(['direction' => $direction], $payload)),
            'content_attributes' => ['data' => $payload],
            'private' => false,
        ];

        if ($user) {
            $data['sender_id'] = $user->id;
            $data['sender_type'] = get_class($user);
        }

        if (! empty($timestamps['created_at'])) {
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
}
