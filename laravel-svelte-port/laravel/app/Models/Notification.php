<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'notification_type',
        'primary_actor_type',
        'primary_actor_id',
        'secondary_actor_type',
        'secondary_actor_id',
        'read_at',
        'snoozed_until',
        'last_activity_at',
        'meta',
        'push_message_title',
    ];

    protected $casts = [
        'notification_type' => 'integer',
        'read_at' => 'datetime',
        'snoozed_until' => 'datetime',
        'last_activity_at' => 'datetime',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Notification $notification) {
            if (!$notification->last_activity_at) {
                $notification->last_activity_at = now();
            }
        });

        static::created(function (Notification $notification) {
            \App\Events\Notification\NotificationCreated::dispatch($notification);
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'account_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'push_event_data',
        'push_message_title',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryActor(): MorphTo
    {
        return $this->morphTo();
    }

    public function secondaryActor(): MorphTo
    {
        return $this->morphTo();
    }

    public function getPushEventDataAttribute()
    {
        // ... (implementation pending, for now returning minimal data)
        return [];
    }

    public function getPushMessageTitleAttribute(): string
    {
        $type = $this->notification_type_string;
        $primaryActor = $this->primaryActor;
        $conversation = $primaryActor instanceof \App\Models\Conversation ? $primaryActor : null;
        
        $displayId = $conversation ? $conversation->display_id : ($primaryActor->id ?? '');
        $inboxName = $conversation && $conversation->inbox ? $conversation->inbox->name : 'Inbox';
        
        return match ($type) {
            'conversation_creation' => "A new conversation [ID - {$displayId}] has been created in {$inboxName}",
            'conversation_assignment' => "A new conversation [ID - {$displayId}] has been assigned to you.",
            'conversation_mention' => "You have been mentioned in conversation [ID - {$displayId}]",
            'assigned_conversation_new_message' => "New message in your assigned conversation [ID - {$displayId}].",
            'participating_conversation_new_message' => "New message in your participating conversation [ID - {$displayId}].",
            'sla_missed_first_response' => "SLA missed for first response in conversation [ID - {$displayId}]",
            'sla_missed_next_response' => "SLA missed for next response in conversation [ID - {$displayId}]",
            'sla_missed_resolution' => "SLA missed for resolution in conversation [ID - {$displayId}]",
            default => "Notification for conversation [ID - {$displayId}]",
        };
    }

    public function getPushMessageBodyAttribute(): string
    {
        $secondaryActor = $this->secondaryActor;
        
        // If secondary actor is a message, use its content (truncated)
        if ($secondaryActor instanceof \App\Models\Message) {
            return Str::limit($secondaryActor->content ?? 'New attachment', 100);
        }

        // Default body
        return "Click to view details";
    }

    public function getNotificationTypeStringAttribute(): ?string
    {
        $types = array_flip(NotificationSetting::NOTIFICATION_TYPES);
        return $types[$this->notification_type] ?? null;
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }
}

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread()
    {
        return $this->read_at === null;
    }

    /**
     * Get the push event data for the notification.
     *
     * @return array
     */
    public function getPushEventDataAttribute(): array
    {
        $data = [
            'id' => $this->id,
            'notification_type' => $this->notification_type_string,
            'primary_actor_id' => $this->primary_actor_id,
            'primary_actor_type' => $this->primary_actor_type,
        ];

        if ($this->primaryActor && method_exists($this->primaryActor, 'pushEventData')) {
            $actorData = $this->primaryActor->pushEventData();
            // Rails slices 'conversation_id' and 'id', we can keep it simple or match it
            $data['primary_actor'] = $actorData;
        } elseif ($this->primaryActor instanceof Conversation) {
             // Fallback for Conversation if pushEventData not defined
             $data['primary_actor'] = [
                 'id' => $this->primaryActor->id,
                 'conversation_id' => $this->primaryActor->display_id,
             ];
        }

        return $data;
    }

    /**
     * Get the string representation of the notification type.
     */
    public function getNotificationTypeStringAttribute(): string
    {
        $types = array_flip(NotificationSetting::NOTIFICATION_TYPES);
        return $types[$this->notification_type] ?? 'unknown';
    }
}
