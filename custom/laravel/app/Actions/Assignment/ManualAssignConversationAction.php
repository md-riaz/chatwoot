<?php

namespace App\Actions\Assignment;

use App\Events\Conversation\ConversationAssigned;
use App\Events\Conversation\ConversationUpdated;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class ManualAssignConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation, User $assignee): Conversation
    {
        $previousAssignee = $conversation->assignee;

        $this->conversationRepository->update($conversation->id, [
            'assignee_id' => $assignee->id,
        ]);

        $conversation = $conversation->fresh();

        if ($previousAssignee?->id !== $assignee->id) {
            event(new ConversationAssigned($conversation, $assignee, $previousAssignee));
            event(new ConversationUpdated($conversation, [
                'assignee_id' => [
                    'previous' => $previousAssignee?->id,
                    'current' => $assignee->id,
                ],
            ]));
        }

        return $conversation;
    }
}
