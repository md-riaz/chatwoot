<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxCapacityLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_capacity_policy_id',
        'inbox_id',
        'conversation_limit',
    ];

    protected $casts = [
        'conversation_limit' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ensure conversation_limit is positive
            if ($model->conversation_limit <= 0) {
                throw new \InvalidArgumentException('Conversation limit must be greater than 0');
            }
        });

        static::updating(function ($model) {
            // Ensure conversation_limit is positive
            if ($model->conversation_limit <= 0) {
                throw new \InvalidArgumentException('Conversation limit must be greater than 0');
            }
        });
    }

    public function agentCapacityPolicy(): BelongsTo
    {
        return $this->belongsTo(AgentCapacityPolicy::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    /**
     * Get the current conversation count for agents with this capacity policy in this inbox
     */
    public function getCurrentConversationCount(User $user): int
    {
        return $user->assignedConversations()
            ->where('inbox_id', $this->inbox_id)
            ->where('status', '!=', \App\Models\Conversation::STATUS_RESOLVED)
            ->count();
    }

    /**
     * Check if the limit is reached for a specific user
     */
    public function isLimitReached(User $user): bool
    {
        return $this->getCurrentConversationCount($user) >= $this->conversation_limit;
    }

    /**
     * Get remaining capacity for a user
     */
    public function getRemainingCapacity(User $user): int
    {
        $current = $this->getCurrentConversationCount($user);
        return max(0, $this->conversation_limit - $current);
    }
}
