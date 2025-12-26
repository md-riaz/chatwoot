<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Models\User;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation, ?User $assignee): Conversation
    {
        $previousAssignee = $conversation->assignee;

        $this->conversationRepository->update($conversation->id, [
            'assignee_id' => $assignee?->id,
        ]);

        // Trigger event
        // event(new ConversationAssigned($conversation, $assignee, $previousAssignee));

        return $conversation->fresh();
    }
}
