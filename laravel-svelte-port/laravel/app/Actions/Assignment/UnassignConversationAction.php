<?php

namespace App\Actions\Assignment;

use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UnassignConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation): Conversation
    {
        $previousAssignee = $conversation->assignee;

        $this->conversationRepository->update($conversation->id, [
            'assignee_id' => null,
        ]);

        // Trigger event
        // event(new ConversationUnassigned($conversation, $previousAssignee));

        return $conversation->fresh();
    }
}
