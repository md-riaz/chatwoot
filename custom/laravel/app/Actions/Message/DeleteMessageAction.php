<?php

namespace App\Actions\Message;

use App\Events\Message\MessageDeleted;
use App\Models\Message;
use App\Repositories\Message\MessageRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteMessageAction
{
    use AsAction;

    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    public function handle(Message $message): bool
    {
        event(new MessageDeleted($message));

        return $this->messageRepository->delete($message->id);
    }
}
