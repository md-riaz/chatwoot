<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class CloseConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation): Conversation
    {
        $previousStatus = $conversation->status;

        $this->conversationRepository->update($conversation->id, [
            'status' => Conversation::STATUS_RESOLVED,
        ]);

        // Trigger event
        // event(new ConversationStatusChanged($conversation, $previousStatus, Conversation::STATUS_RESOLVED));

        return $conversation->fresh();
    }
}
