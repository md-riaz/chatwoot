<?php

namespace App\Services;

use App\Models\AgentCapacityPolicy;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AgentCapacityService
{
    /**
     * Get available agents for assignment based on capacity policies
     */
    public function getAvailableAgents(Inbox $inbox, ?Conversation $conversation = null): Collection
    {
        // Get all agents assigned to this inbox
        $agents = $inbox->members()
            ->whereHas('accountUsers', function ($query) use ($inbox) {
                $query->where('account_id', $inbox->account_id)
                    ->where('availability', 'online')
                    ->where('active_at', true);
            })
            ->get();

        // Filter agents based on capacity policies
        return $agents->filter(function ($agent) use ($inbox, $conversation) {
            return $this->canAgentTakeConversation($agent, $inbox, $conversation);
        });
    }

    /**
     * Check if an agent can take a new conversation based on capacity policy
     */
    public function canAgentTakeConversation(User $agent, Inbox $inbox, ?Conversation $conversation = null): bool
    {
        $accountUser = $agent->accountUsers()
            ->where('account_id', $inbox->account_id)
            ->first();

        if (!$accountUser || !$accountUser->agent_capacity_policy_id) {
            // No capacity policy assigned, agent can take conversation
            return true;
        }

        $capacityPolicy = $accountUser->agentCapacityPolicy;
        
        if (!$capacityPolicy) {
            return true;
        }

        // Check inbox-specific capacity limits
        if (!$this->checkInboxCapacityLimit($agent, $inbox, $capacityPolicy)) {
            return false;
        }

        // Apply exclusion rules if conversation is provided
        if ($conversation && !$this->passesExclusionRules($conversation, $capacityPolicy)) {
            return false;
        }

        return true;
    }

    /**
     * Check if agent is within inbox capacity limit
     */
    private function checkInboxCapacityLimit(User $agent, Inbox $inbox, AgentCapacityPolicy $capacityPolicy): bool
    {
        $inboxLimit = $capacityPolicy->inboxCapacityLimits()
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$inboxLimit) {
            // No specific limit for this inbox
            return true;
        }

        return !$inboxLimit->isLimitReached($agent);
    }

    /**
     * Check if conversation passes exclusion rules
     */
    private function passesExclusionRules(Conversation $conversation, AgentCapacityPolicy $capacityPolicy): bool
    {
        $exclusionRules = $capacityPolicy->exclusion_rules ?? [];

        // Check excluded labels
        if (isset($exclusionRules['excluded_labels']) && !empty($exclusionRules['excluded_labels'])) {
            $excludedLabels = $exclusionRules['excluded_labels'];
            $conversationLabels = $conversation->labels->pluck('title')->toArray();
            
            if (array_intersect($conversationLabels, $excludedLabels)) {
                return false; // Conversation has excluded labels
            }
        }

        // Check time-based exclusion
        if (isset($exclusionRules['exclude_older_than_hours'])) {
            $hours = $exclusionRules['exclude_older_than_hours'];
            $cutoffTime = now()->subHours($hours);
            
            if ($conversation->created_at < $cutoffTime) {
                return false; // Conversation is too old
            }
        }

        return true;
    }

    /**
     * Get agent capacity statistics
     */
    public function getAgentCapacityStats(User $agent, Inbox $inbox): array
    {
        $accountUser = $agent->accountUsers()
            ->where('account_id', $inbox->account_id)
            ->first();

        if (!$accountUser || !$accountUser->agent_capacity_policy_id) {
            return [
                'has_capacity_policy' => false,
                'current_conversations' => $this->getCurrentConversationCount($agent, $inbox),
                'limit' => null,
                'remaining_capacity' => null,
                'at_capacity' => false,
            ];
        }

        $capacityPolicy = $accountUser->agentCapacityPolicy;
        $inboxLimit = $capacityPolicy->inboxCapacityLimits()
            ->where('inbox_id', $inbox->id)
            ->first();

        $currentCount = $this->getCurrentConversationCount($agent, $inbox);

        if (!$inboxLimit) {
            return [
                'has_capacity_policy' => true,
                'current_conversations' => $currentCount,
                'limit' => null,
                'remaining_capacity' => null,
                'at_capacity' => false,
            ];
        }

        $remainingCapacity = max(0, $inboxLimit->conversation_limit - $currentCount);

        return [
            'has_capacity_policy' => true,
            'current_conversations' => $currentCount,
            'limit' => $inboxLimit->conversation_limit,
            'remaining_capacity' => $remainingCapacity,
            'at_capacity' => $remainingCapacity === 0,
        ];
    }

    /**
     * Get current conversation count for agent in inbox
     */
    private function getCurrentConversationCount(User $agent, Inbox $inbox): int
    {
        return $agent->assignedConversations()
            ->where('inbox_id', $inbox->id)
            ->where('status', '!=', 'resolved')
            ->count();
    }

    /**
     * Get agents grouped by their capacity status
     */
    public function getAgentsByCapacityStatus(Inbox $inbox): array
    {
        $agents = $inbox->members()
            ->whereHas('accountUsers', function ($query) use ($inbox) {
                $query->where('account_id', $inbox->account_id);
            })
            ->get();

        $available = [];
        $atCapacity = [];
        $noPolicy = [];

        foreach ($agents as $agent) {
            $stats = $this->getAgentCapacityStats($agent, $inbox);
            
            if (!$stats['has_capacity_policy']) {
                $noPolicy[] = $agent;
            } elseif ($stats['at_capacity']) {
                $atCapacity[] = $agent;
            } else {
                $available[] = $agent;
            }
        }

        return [
            'available' => collect($available),
            'at_capacity' => collect($atCapacity),
            'no_policy' => collect($noPolicy),
        ];
    }

    /**
     * Validate capacity policy exclusion rules
     */
    public function validateExclusionRules(array $rules): array
    {
        $errors = [];

        if (isset($rules['overall_capacity'])) {
            if (!is_int($rules['overall_capacity']) || $rules['overall_capacity'] <= 0) {
                $errors['overall_capacity'] = 'Overall capacity must be a positive integer';
            }
        }

        if (isset($rules['exclude_older_than_hours'])) {
            if (!is_int($rules['exclude_older_than_hours']) || $rules['exclude_older_than_hours'] <= 0) {
                $errors['exclude_older_than_hours'] = 'Exclude older than hours must be a positive integer';
            }
        }

        if (isset($rules['excluded_labels'])) {
            if (!is_array($rules['excluded_labels'])) {
                $errors['excluded_labels'] = 'Excluded labels must be an array';
            }
        }

        return $errors;
    }
}