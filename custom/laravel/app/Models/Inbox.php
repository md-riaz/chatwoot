<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inbox extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'name',
        'channel_type',
        'channel_id',
        'enable_auto_assignment',
        'greeting_enabled',
        'greeting_message',
        'enable_email_collect',
        'csat_survey_enabled',
        'allow_messages_after_resolved',
        'working_hours',
        'timezone',
        'working_hours_enabled',
        'out_of_office_message',
    ];

    protected $casts = [
        'enable_auto_assignment' => 'boolean',
        'greeting_enabled' => 'boolean',
        'enable_email_collect' => 'boolean',
        'csat_survey_enabled' => 'boolean',
        'allow_messages_after_resolved' => 'boolean',
        'working_hours' => 'array',
        'working_hours_enabled' => 'boolean',
    ];

    /**
     * Get the account that owns the inbox.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the channel for the inbox (polymorphic).
     */
    public function channel(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all conversations for the inbox.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get all contact inboxes for the inbox.
     */
    public function contactInboxes(): HasMany
    {
        return $this->hasMany(ContactInbox::class);
    }

    /**
     * Get all members (agents) for the inbox.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inbox_members')
            ->withTimestamps();
    }
}
