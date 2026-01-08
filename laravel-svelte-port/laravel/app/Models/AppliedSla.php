<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppliedSla extends Model
{
    use HasFactory;

    // SLA Status constants
    public const STATUS_ACTIVE = 0;
    public const STATUS_HIT = 1;
    public const STATUS_MISSED = 2;
    public const STATUS_ACTIVE_WITH_MISSES = 3;

    protected $fillable = [
        'account_id',
        'sla_policy_id',
        'conversation_id',
        'sla_first_response_at',
        'sla_next_response_at',
        'sla_resolution_at',
        'sla_status',
    ];

    protected $casts = [
        'sla_first_response_at' => 'datetime',
        'sla_next_response_at' => 'datetime',
        'sla_resolution_at' => 'datetime',
        'sla_status' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (AppliedSla $appliedSla) {
            if (empty($appliedSla->account_id)) {
                $appliedSla->account_id = $appliedSla->slaPolicy?->account_id;
            }
        });

        static::updated(function (AppliedSla $appliedSla) {
            if ($appliedSla->wasChanged('sla_status')) {
                $appliedSla->pushConversationEvent();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function slaEvents(): HasMany
    {
        return $this->hasMany(SlaEvent::class);
    }

    // Scopes
    public function scopeFilterByDateRange($query, $range)
    {
        if ($range) {
            return $query->whereBetween('created_at', $range);
        }
        return $query;
    }

    public function scopeFilterByInboxId($query, $inboxId)
    {
        if ($inboxId) {
            return $query->whereHas('conversation', function ($q) use ($inboxId) {
                $q->where('inbox_id', $inboxId);
            });
        }
        return $query;
    }

    public function scopeFilterByTeamId($query, $teamId)
    {
        if ($teamId) {
            return $query->whereHas('conversation', function ($q) use ($teamId) {
                $q->where('team_id', $teamId);
            });
        }
        return $query;
    }

    public function scopeFilterBySlaPolicy($query, $slaPolicyId)
    {
        if ($slaPolicyId) {
            return $query->where('sla_policy_id', $slaPolicyId);
        }
        return $query;
    }

    public function scopeFilterByLabelList($query, $labelList)
    {
        if ($labelList) {
            return $query->whereHas('conversation', function ($q) use ($labelList) {
                $q->where('cached_label_list', 'LIKE', "%{$labelList}%");
            });
        }
        return $query;
    }

    public function scopeFilterByAssignedAgent($query, $assignedAgentId)
    {
        if ($assignedAgentId) {
            return $query->whereHas('conversation', function ($q) use ($assignedAgentId) {
                $q->where('assignee_id', $assignedAgentId);
            });
        }
        return $query;
    }

    public function scopeMissed($query)
    {
        return $query->whereIn('sla_status', [self::STATUS_MISSED, self::STATUS_ACTIVE_WITH_MISSES]);
    }

    // Status helper methods
    public function isActive(): bool
    {
        return $this->sla_status === self::STATUS_ACTIVE;
    }

    public function isHit(): bool
    {
        return $this->sla_status === self::STATUS_HIT;
    }

    public function isMissed(): bool
    {
        return $this->sla_status === self::STATUS_MISSED;
    }

    public function isActiveWithMisses(): bool
    {
        return $this->sla_status === self::STATUS_ACTIVE_WITH_MISSES;
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->sla_status) {
            self::STATUS_ACTIVE => 'active',
            self::STATUS_HIT => 'hit',
            self::STATUS_MISSED => 'missed',
            self::STATUS_ACTIVE_WITH_MISSES => 'active_with_misses',
            default => 'unknown',
        };
    }

    /**
     * Get push event data for real-time updates
     */
    public function pushEventData(): array
    {
        return [
            'id' => $this->id,
            'sla_id' => $this->sla_policy_id,
            'sla_status' => $this->status_name,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'sla_description' => $this->slaPolicy->description,
            'sla_name' => $this->slaPolicy->name,
            'sla_first_response_time_threshold' => $this->slaPolicy->first_response_time_threshold,
            'sla_next_response_time_threshold' => $this->slaPolicy->next_response_time_threshold,
            'sla_only_during_business_hours' => $this->slaPolicy->only_during_business_hours,
            'sla_resolution_time_threshold' => $this->slaPolicy->resolution_time_threshold,
        ];
    }

    /**
     * Push conversation event for real-time updates
     */
    private function pushConversationEvent(): void
    {
        // Dispatch conversation updated event for real-time updates
        // This would integrate with Laravel's broadcasting system
        if ($this->conversation) {
            // TODO: Implement conversation event broadcasting
            // event(new ConversationUpdated($this->conversation));
        }
    }
}
