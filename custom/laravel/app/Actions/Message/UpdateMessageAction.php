<?php

namespace App\Actions\Message;

use App\Events\Message\MessageUpdated;
use App\Models\Message;
use App\Repositories\Message\MessageRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMessageAction
{
    use AsAction;

    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    public function handle(Message $message, array $data): Message
    {
        $this->messageRepository->update($message->id, $data);

        $message = $message->fresh();

        if (! $message) {
            throw new \RuntimeException('Message was not found after update.');
        }

        event(new MessageUpdated($message));

        return $message;
    }
}
