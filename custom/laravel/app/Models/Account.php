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
     * Get the account user relationships.
     */
    public function accountUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccountUser::class);
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
     * Get the SAML settings for the account.
     */
    public function samlSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AccountSamlSetting::class);
    }

    /**
     * Get all applied SLAs for the account.
     */
    public function appliedSlas(): HasMany
    {
        return $this->hasMany(AppliedSla::class);
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
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Check if account has premium access.
     */
    public function isPremium(): bool
    {
        // For now, assume all accounts are premium
        // In production, this would check subscription status
        return true;
    }

    /**
     * Enable a feature for this account.
     */
    public function enableFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        
        if (!in_array($feature, $features)) {
            $features[] = $feature;
            $this->features = $features;
            $this->save();
        }
        
        return true;
    }

    /**
     * Disable a feature for this account.
     */
    public function disableFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        
        if (in_array($feature, $features)) {
            $features = array_values(array_diff($features, [$feature]));
            $this->features = $features;
            $this->save();
        }
        
        return true;
    }

    /**
     * Get all enabled features for this account.
     */
    public function getEnabledFeatures(): array
    {
        return $this->features ?? [];
    }

    /**
     * Return usage limits structure used by services (minimal implementation).
     */
    public function usageLimits(): array
    {
        $responses = $this->getCaptainLimits('responses');

        return [
            'captain' => [
                'documents' => $this->getCaptainLimits('documents'),
                'responses' => $responses,
            ],
        ];
    }

    /**
     * Increment captain response usage counter stored in custom_attributes.
     */
    public function incrementResponseUsage(): void
    {
        $key = 'captain_responses_usage';
        $attrs = $this->custom_attributes ?? [];
        $current = isset($attrs[$key]) ? (int) $attrs[$key] : 0;
        $attrs[$key] = $current + 1;
        $this->custom_attributes = $attrs;
        $this->save();
    }

    /**
     * Compute captain limits for a given type.
     */
    protected function getCaptainLimits(string $type): array
    {
        // total_count may be stored under limits; fallback to a large value
        $total = null;
        if (is_array($this->limits)) {
            if (isset($this->limits[$type])) {
                $total = (int) $this->limits[$type];
            } elseif (isset($this->limits['captain']) && isset($this->limits['captain'][$type])) {
                $total = (int) $this->limits['captain'][$type];
            }
        }

        // consumed stored in custom_attributes
        $usageKey = $type === 'responses' ? 'captain_responses_usage' : 'captain_documents_usage';
        $consumed = isset($this->custom_attributes[$usageKey]) ? (int) $this->custom_attributes[$usageKey] : 0;

        if ($total === null) {
            // no configured limit -> treat as unlimited
            $total = PHP_INT_MAX;
        }

        $available = max(0, $total - $consumed);

        return [
            'total_count' => $total,
            'current_available' => $available,
            'consumed' => $consumed,
        ];
    }

    public function autoResolveAfterMinutes(): ?int
    {
        $value = data_get($this->settings, 'auto_resolve_after');

        return $value !== null ? (int) $value : null;
    }

    public function autoResolveIgnoreWaiting(): bool
    {
        return (bool) data_get($this->settings, 'auto_resolve_ignore_waiting', false);
    }
}
