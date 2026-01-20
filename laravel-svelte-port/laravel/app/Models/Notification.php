<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
