<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation, array $data): Conversation
    {
        $this->conversationRepository->update($conversation->id, $data);

        // Trigger event
        // event(new ConversationUpdated($conversation));

        return $conversation->fresh();
    }
}
