<?php

namespace App\Actions\Assignment;

use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use App\Repositories\Conversation\ConversationRepository;
use App\Repositories\Inbox\InboxRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class AutoAssignConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository,
        private InboxRepository $inboxRepository
    ) {}

    public function handle(int $conversationId): ?User
    {
        $conversation = $this->conversationRepository->find($conversationId);

        if (! $conversation || $conversation->assignee_id) {
            return null;
        }

        $inbox = $this->inboxRepository->find($conversation->inbox_id);

        if (! $inbox || ! $inbox->enable_auto_assignment) {
            return null;
        }

        $agent = $this->findBestAgent($inbox);

        if ($agent) {
            $this->conversationRepository->update($conversation->id, [
                'assignee_id' => $agent->id,
            ]);
            // event(new ConversationAssigned($conversation, $agent));
        }

        return $agent;
    }

    private function findBestAgent(Inbox $inbox): ?User
    {
        // Get agents assigned to this inbox who are online
        $inboxMembers = $inbox->members()->where('availability', 1)->get();

        if ($inboxMembers->isEmpty()) {
            // Fallback to account-level users
            return $inbox->account
                ->users()
                ->wherePivot('availability', 1)
                ->inRandomOrder()
                ->first();
        }

        // Get conversation counts for load balancing
        $openCounts = $this->conversationRepository->countOpenByAssignee($inbox->account_id);

        // Find agent with least conversations (load-based assignment)
        return $inboxMembers->sortBy(function ($agent) use ($openCounts) {
            return $openCounts[$agent->id] ?? 0;
        })->first();
    }

    /**
     * Run as listener for ConversationCreated event.
     */
    public function asListener($event): void
    {
        $this->handle($event->conversation->id);
    }
}
