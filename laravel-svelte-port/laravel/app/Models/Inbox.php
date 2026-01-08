<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inbox extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'portal_id',
        'name',
        'channel_type',
        'channel_id',
        'enable_auto_assignment',
        'greeting_enabled',
        'greeting_message',
        'enable_email_collect',
        'csat_survey_enabled',
        'allow_messages_after_resolved',
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
        'working_hours_enabled' => 'boolean',
        'working_hours' => 'array',
    ];

    /**
     * Get the account that owns the inbox.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the portal for the inbox.
     */
    public function portal(): BelongsTo
    {
        return $this->belongsTo(Portal::class);
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

    /**
     * Alias for members() for backward compatibility.
     */
    public function users(): BelongsToMany
    {
        return $this->members();
    }

    /**
     * Get the assignment policy for the inbox.
     */
    public function inboxAssignmentPolicy(): HasOne
    {
        return $this->hasOne(InboxAssignmentPolicy::class);
    }

    /**
     * Get the assignment policy through the junction table.
     */
    public function assignmentPolicy(): BelongsTo
    {
        return $this->belongsTo(AssignmentPolicy::class, 'assignment_policy_id')
            ->through('inboxAssignmentPolicy');
    }

    /**
     * Get available agents for assignment.
     */
    public function availableAgents()
    {
        return $this->members()->where('availability_status', 'online');
    }

    /**
     * Check if auto assignment v2 is enabled.
     */
    public function getAutoAssignmentV2EnabledAttribute(): bool
    {
        return $this->account->feature_enabled('assignment_v2') ?? false;
    }

    /**
     * Get assignment configuration.
     */
    public function getAssignmentConfigAttribute(): ?array
    {
        return $this->assignmentPolicy?->toArray();
    }

    /**
     * Get all working hours for the inbox.
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /**
     * Get all campaigns for the inbox.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the agent bot inbox relationship.
     */
    public function agentBotInbox()
    {
        return $this->hasOne(\App\Models\AgentBotInbox::class);
    }

    /**
     * Get the agent bot through the agent bot inbox.
     */
    public function getAgentBotAttribute()
    {
        return $this->agentBotInbox?->agentBot;
    }

    /**
     * Check if inbox is currently within working hours.
     */
    public function isOpenNow(): bool
    {
        if (! $this->working_hours_enabled) {
            return true;
        }

        $today = now($this->timezone)->dayOfWeek;
        $workingHour = $this->workingHours()->where('day_of_week', $today)->first();

        return $workingHour ? $workingHour->isOpenNow() : false;
    }

    /**
     * Get assignable agents for the inbox.
     */
    public function assignableAgents()
    {
        return $this->members()->wherePivot('is_active', true);
    }
}
