<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'campaign_id',
        'display_id',
        'status',
        'priority',
        'additional_attributes',
        'custom_attributes',
        'first_reply_created_at',
        'last_activity_at',
        'waiting_since',
        'snoozed_until',
        'muted',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
        'additional_attributes' => 'array',
        'status' => 'integer',
        'priority' => 'integer',
        'first_reply_created_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'waiting_since' => 'datetime',
        'snoozed_until' => 'datetime',
        'muted' => 'boolean',
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
     * Get the campaign for the conversation.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
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
     * Get all participants for the conversation.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withTimestamps();
    }

    /**
     * Get all mentions for the conversation.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    /**
     * Get the CSAT survey response for the conversation.
     */
    public function csatSurveyResponse(): HasOne
    {
        return $this->hasOne(CsatSurveyResponse::class);
    }

    /**
     * Get all attachments for the conversation (through messages).
     */
    public function attachments()
    {
        return Attachment::whereHas('message', function ($query) {
            $query->where('conversation_id', $this->id);
        });
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

    /**
     * Scope a query to only include unattended conversations.
     * Rails parity: scope :unattended, -> { where(first_reply_created_at: nil).or(where.not(waiting_since: nil)) }
     * 
     * Unattended means:
     * - No agent has replied yet (first_reply_created_at is null), OR
     * - Conversation is waiting for agent response (waiting_since is not null)
     */
    public function scopeUnattended($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('first_reply_created_at')
              ->orWhereNotNull('waiting_since');
        });
    }

    /**
     * Scope a query to only include muted conversations.
     */
    public function scopeMuted($query)
    {
        return $query->where('muted', true);
    }

    /**
     * Check if the conversation is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Get conversation participants relationship for SLA notifications
     */
    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get applied SLAs for this conversation
     */
    public function appliedSlas(): HasMany
    {
        return $this->hasMany(AppliedSla::class);
    }

    /**
     * Get SLA events for this conversation
     */
    public function slaEvents(): HasMany
    {
        return $this->hasMany(SlaEvent::class);
    }

    /**
     * Get push event data for real-time updates
     */
    public function pushEventData(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->display_id,
        ];
    }
}
