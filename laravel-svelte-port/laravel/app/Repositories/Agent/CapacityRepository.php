<?php

namespace App\Repositories\Agent;

use App\Models\Inbox;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class CapacityRepository extends BaseRepository
{
    public function __construct()
    {
        // No specific model for this repository as it works with multiple models
    }

    /**
     * Get all agents assigned to an inbox
     */
    public function getInboxAgents(Inbox $inbox): Collection
    {
        return $inbox->members()
            ->whereHas('accountUsers', function ($query) use ($inbox) {
                $query->where('account_id', $inbox->account_id)
                    ->where('availability', 'online')
                    ->where('active_at', true);
            })
            ->get();
    }

    /**
     * Get account user relationship for agent
     */
    public function getAccountUser(User $agent, int $accountId)
    {
        return $agent->accountUsers()
            ->where('account_id', $accountId)
            ->first();
    }

    /**
     * Get inbox capacity limit for a capacity policy
     */
    public function getInboxCapacityLimit($capacityPolicy, Inbox $inbox)
    {
        return $capacityPolicy->inboxCapacityLimits()
            ->where('inbox_id', $inbox->id)
            ->first();
    }

    /**
     * Get current conversation count for agent in inbox
     */
    public function getCurrentConversationCount(User $agent, Inbox $inbox): int
    {
        return $agent->assignedConversations()
            ->where('inbox_id', $inbox->id)
            ->where('status', '!=', \App\Models\Conversation::STATUS_RESOLVED)
            ->count();
    }
}