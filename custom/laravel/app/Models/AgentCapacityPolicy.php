<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentCapacityPolicy extends Model
{
    use HasFactory;

    const MAX_NAME_LENGTH = 255;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'exclusion_rules',
    ];

    protected $casts = [
        'exclusion_rules' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->exclusion_rules)) {
                $model->exclusion_rules = [];
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inboxCapacityLimits(): HasMany
    {
        return $this->hasMany(InboxCapacityLimit::class);
    }

    public function inboxes()
    {
        return $this->belongsToMany(Inbox::class, 'inbox_capacity_limits')
            ->withPivot('conversation_limit')
            ->withTimestamps();
    }

    public function accountUsers(): HasMany
    {
        return $this->hasMany(AccountUser::class);
    }

    /**
     * Get users assigned to this capacity policy through account_users
     */
    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            AccountUser::class,
            'agent_capacity_policy_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * Check if agent is at capacity for a specific inbox
     */
    public function isAgentAtCapacity(User $user, Inbox $inbox): bool
    {
        $limit = $this->inboxCapacityLimits()
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$limit) {
            return false;
        }

        $currentCount = $user->assignedConversations()
            ->where('inbox_id', $inbox->id)
            ->where('status', '!=', 'resolved')
            ->count();

        return $currentCount >= $limit->conversation_limit;
    }

    /**
     * Get available agents for assignment based on capacity rules
     */
    public function getAvailableAgents(Inbox $inbox)
    {
        return $this->users()
            ->whereHas('accountUsers', function ($query) {
                $query->where('availability', 'online')
                    ->where('active_at', true);
            })
            ->get()
            ->filter(function ($user) use ($inbox) {
                return !$this->isAgentAtCapacity($user, $inbox);
            });
    }

    /**
     * Apply exclusion rules to filter conversations
     */
    public function applyExclusionRules($conversations)
    {
        if (empty($this->exclusion_rules)) {
            return $conversations;
        }

        // Apply excluded labels filter
        if (isset($this->exclusion_rules['excluded_labels']) && !empty($this->exclusion_rules['excluded_labels'])) {
            $excludedLabels = $this->exclusion_rules['excluded_labels'];
            $conversations = $conversations->whereDoesntHave('labels', function ($query) use ($excludedLabels) {
                $query->whereIn('title', $excludedLabels);
            });
        }

        // Apply time-based exclusion
        if (isset($this->exclusion_rules['exclude_older_than_hours'])) {
            $hours = $this->exclusion_rules['exclude_older_than_hours'];
            $cutoffTime = now()->subHours($hours);
            $conversations = $conversations->where('created_at', '>=', $cutoffTime);
        }

        // Apply overall capacity limit
        if (isset($this->exclusion_rules['overall_capacity'])) {
            $limit = $this->exclusion_rules['overall_capacity'];
            $conversations = $conversations->limit($limit);
        }

        return $conversations;
    }
}
