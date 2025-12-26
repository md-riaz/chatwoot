<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_OPEN = 0;
    public const STATUS_RESOLVED = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_SNOOZED = 3;

    // Priority constants
    public const PRIORITY_NONE = 0;
    public const PRIORITY_LOW = 1;
    public const PRIORITY_MEDIUM = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_URGENT = 4;

    protected $fillable = [
        'account_id',
        'inbox_id',
        'contact_id',
        'contact_inbox_id',
        'assignee_id',
        'team_id',
        'display_id',
        'status',
        'priority',
        'additional_attributes',
        'custom_attributes',
        'first_reply_created_at',
        'last_activity_at',
        'waiting_since',
        'snoozed_until',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
        'status' => 'integer',
        'priority' => 'integer',
        'first_reply_created_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'waiting_since' => 'datetime',
        'snoozed_until' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($conversation) {
            $conversation->uuid = (string) Str::uuid();
        });
    }

    /**
     * Get the account that owns the conversation.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the inbox that owns the conversation.
     */
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    /**
     * Get the contact that owns the conversation.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the assignee (user) for the conversation.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the team for the conversation.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all labels for the conversation.
     */
    public function labels(): MorphToMany
    {
        return $this->morphToMany(Label::class, 'labelable', 'labelings');
    }

    /**
     * Scope a query to only include open conversations.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include unassigned conversations.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assignee_id');
    }
}
