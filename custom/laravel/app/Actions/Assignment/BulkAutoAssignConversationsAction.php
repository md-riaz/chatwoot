<?php

namespace App\Actions\Assignment;

use App\Models\Inbox;
use App\Repositories\Conversation\ConversationRepository;
use Illuminate\Support\Facades\Log;

class BulkAutoAssignConversationsAction
{
    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Inbox $inbox, int $limit = 100): int
    {
        if (! $inbox->auto_assignment_v2_enabled) {
            return 0;
        }

        $assignedCount = 0;
        $conversations = $this->unassignedConversations($inbox, $limit);
        foreach ($conversations as $conversation) {
            if ($this->assignIfPossible($inbox, $conversation)) {
                $assignedCount++;
            }
        }
        return $assignedCount;
    }

    private function unassignedConversations(Inbox $inbox, int $limit)
    {
        $query = $inbox->conversations()->whereNull('assignee_id')->where('status', 'open');
        $config = $inbox->assignment_config;
        if (($config['conversation_priority'] ?? null) === 'longest_waiting') {
            $query->orderBy('last_activity_at')->orderBy('created_at');
        } else {
            $query->orderBy('created_at');
        }
        return $query->limit($limit)->get();
    }

    private function assignIfPossible(Inbox $inbox, $conversation): bool
    {
        if ($conversation->assignee_id || $conversation->status !== \App\Models\Conversation::STATUS_OPEN) {
            return false;
        }
        $agent = $this->findAvailableAgent($inbox);
        if (! $agent) {
            return false;
        }
        $conversation->assignee_id = $agent->id;
        $conversation->save();
        // Optionally: event(new ConversationAssigned($conversation, $agent));
        return true;
    }

    private function findAvailableAgent(Inbox $inbox)
    {
        $agents = $this->filterAgentsByRateLimit($inbox->available_agents);
        if (empty($agents)) {
            return null;
        }
        
        $roundRobinService = new \App\Services\AutoAssignment\RoundRobinService($inbox);
        $agentIds = collect($agents)->pluck('id')->map(fn($id) => (string) $id)->toArray();
        
        return $roundRobinService->selectAgent($agentIds);
    }

    private function filterAgentsByRateLimit($agents)
    {
        if (empty($agents)) {
            return $agents;
        }
        
        return collect($agents)->filter(function ($agent) {
            $rateLimiter = new \App\Services\AutoAssignment\RateLimiter($this->inbox, $agent);
            return $rateLimiter->withinLimit();
        })->values()->toArray();
    }
}
