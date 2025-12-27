<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'locale',
        'domain',
        'support_email',
        'settings',
        'custom_attributes',
        'features',
        'limits',
        'status',
        'conversation_required_attributes',
    ];

    protected $casts = [
        'settings' => 'array',
        'custom_attributes' => 'array',
        'features' => 'array',
        'limits' => 'array',
        'status' => 'integer',
        'conversation_required_attributes' => 'array',
    ];

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'locale', 'domain', 'support_email', 'status'])
            ->logOnlyDirty();
    }

    /**
     * Get the users that belong to the account.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'account_users')
            ->withPivot('role', 'availability', 'settings', 'active_at')
            ->withTimestamps();
    }

    /**
     * Get all inboxes for the account.
     */
    public function inboxes(): HasMany
    {
        return $this->hasMany(Inbox::class);
    }

    /**
     * Get all conversations for the account.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get all contacts for the account.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get all teams for the account.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get all labels for the account.
     */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /**
     * Get all canned responses for the account.
     */
    public function cannedResponses(): HasMany
    {
        return $this->hasMany(CannedResponse::class);
    }

    /**
     * Get all automation rules for the account.
     */
    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class);
    }

    /**
     * Get all webhooks for the account.
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * Get all campaigns for the account.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get all macros for the account.
     */
    public function macros(): HasMany
    {
        return $this->hasMany(Macro::class);
    }

    /**
     * Get all agent bots for the account.
     */
    public function agentBots(): HasMany
    {
        return $this->hasMany(AgentBot::class);
    }

    /**
     * Get all custom filters for the account.
     */
    public function customFilters(): HasMany
    {
        return $this->hasMany(CustomFilter::class);
    }

    /**
     * Get all portals (help centers) for the account.
     */
    public function portals(): HasMany
    {
        return $this->hasMany(Portal::class);
    }

    /**
     * Get all custom attribute definitions for the account.
     */
    public function customAttributeDefinitions(): HasMany
    {
        return $this->hasMany(CustomAttributeDefinition::class);
    }

    /**
     * Get all reporting events for the account.
     */
    public function reportingEvents(): HasMany
    {
        return $this->hasMany(ReportingEvent::class);
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Check if a feature is enabled.
     */
    public function featureEnabled(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }
}
