<?php

namespace App\Models;

use App\Actions\Notifications\CreateSlaNotificationAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaEvent extends Model
{
    use HasFactory;

    // Event types
    public const TYPE_FRT = 0; // First Response Time
    public const TYPE_NRT = 1; // Next Response Time  
    public const TYPE_RT = 2;  // Resolution Time

    protected $fillable = [
        'applied_sla_id',
        'conversation_id',
        'account_id',
        'sla_policy_id',
        'inbox_id',
        'event_type',
        'meta',
    ];

    protected $casts = [
        'event_type' => 'integer',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (SlaEvent $slaEvent) {
            $slaEvent->ensureRequiredFields();
        });

        static::created(function (SlaEvent $slaEvent) {
            $slaEvent->createNotifications();
        });
    }

    public function appliedSla(): BelongsTo
    {
        return $this->belongsTo(AppliedSla::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    /**
     * Get the event type name
     */
    public function getEventTypeNameAttribute(): string
    {
        return match ($this->event_type) {
            self::TYPE_FRT => 'frt',
            self::TYPE_NRT => 'nrt',
            self::TYPE_RT => 'rt',
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
            'event_type' => $this->event_type_name,
            'meta' => $this->meta,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }

    /**
     * Ensure all required fields are populated before creation
     */
    private function ensureRequiredFields(): void
    {
        if (empty($this->applied_sla_id) && $this->conversation_id) {
            $appliedSla = AppliedSla::where('conversation_id', $this->conversation_id)->latest()->first();
            $this->applied_sla_id = $appliedSla?->id;
        }

        if (empty($this->account_id) && $this->conversation) {
            $this->account_id = $this->conversation->account_id;
        }

        if (empty($this->inbox_id) && $this->conversation) {
            $this->inbox_id = $this->conversation->inbox_id;
        }

        if (empty($this->sla_policy_id) && $this->appliedSla) {
            $this->sla_policy_id = $this->appliedSla->sla_policy_id;
        }
    }

    /**
     * Create notifications for SLA events
     */
    private function createNotifications(): void
    {
        if (!$this->conversation || !$this->account || !$this->slaPolicy) {
            return;
        }

        // Get users to notify
        $notifyUsers = collect();

        // Add conversation participants
        if ($this->conversation->conversationParticipants) {
            $participantUsers = $this->conversation->conversationParticipants()
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();
            $notifyUsers = $notifyUsers->merge($participantUsers);
        }

        // Add account administrators
        $administrators = $this->account->users()
            ->wherePivot('role', 'administrator')
            ->get();
        $notifyUsers = $notifyUsers->merge($administrators);

        // Add conversation assignee
        if ($this->conversation->assignee) {
            $notifyUsers->push($this->conversation->assignee);
        }

        // Get notification type based on event type
        $notificationType = match ($this->event_type_name) {
            'frt' => 'sla_missed_first_response',
            'nrt' => 'sla_missed_next_response',
            'rt' => 'sla_missed_resolution',
            default => null,
        };

        if (!$notificationType) {
            return;
        }

        // Create notifications for unique users
        $notifyUsers->unique('id')->each(function ($user) use ($notificationType) {
            CreateSlaNotificationAction::run(
                $notificationType,
                $user,
                $this->account,
                $this->conversation,
                $this->slaPolicy
            );
        });
    }
}
