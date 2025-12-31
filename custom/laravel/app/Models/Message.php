<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    // Message type constants
    public const TYPE_INCOMING = 0;

    public const TYPE_OUTGOING = 1;

    public const TYPE_ACTIVITY = 2;

    public const TYPE_TEMPLATE = 3;

    // Content type constants
    public const CONTENT_TEXT = 0;

    public const CONTENT_INPUT_TEXT = 1;

    public const CONTENT_INPUT_EMAIL = 2;

    public const CONTENT_INPUT_SELECT = 3;

    public const CONTENT_CARDS = 4;

    public const CONTENT_FORM = 5;

    public const CONTENT_ARTICLE = 6;
    public const CONTENT_VOICE_CALL = 12;

    // Status constants
    public const STATUS_SENT = 0;

    public const STATUS_DELIVERED = 1;

    public const STATUS_READ = 2;

    public const STATUS_FAILED = 3;

    protected $fillable = [
        'account_id',
        'conversation_id',
        'inbox_id',
        'sender_id',
        'sender_type',
        'message_type',
        'content',
        'content_attributes',
        'content_type',
        'status',
        'private',
        'external_source_id',
        'external_source_ids',
        'source_id',
        'translations',
    ];

    protected $casts = [
        'content_attributes' => 'array',
        'external_source_ids' => 'array',
        'translations' => 'array',
        'message_type' => 'integer',
        'content_type' => 'integer',
        'status' => 'integer',
        'private' => 'boolean',
    ];

    /**
     * Get the account that owns the message.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the inbox that owns the message.
     */
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    /**
     * Get the sender (polymorphic: User or Contact).
     */
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all attachments for the message.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get all media files for the message.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Dispatch message updated event so realtime clients receive changes.
     */
    public function sendUpdateEvent(): void
    {
        try {
            event(new \App\Events\Message\MessageUpdated($this));
        } catch (\Exception $e) {
            // swallow; caller may handle logging
        }
    }

    /**
     * Reindex message for search backends (noop if no search service).
     */
    public function reindex(): void
    {
        if (class_exists(\App\Services\SearchService::class)) {
            try {
                $svc = app(\App\Services\SearchService::class);
                if (method_exists($svc, 'indexMessage')) {
                    $svc->indexMessage($this);
                }
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Scope a query to only include public messages.
     */
    public function scopePublic($query)
    {
        return $query->where('private', false);
    }

    /**
     * Scope a query to only include private notes.
     */
    public function scopePrivate($query)
    {
        return $query->where('private', true);
    }
}
