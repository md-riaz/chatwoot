<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\Locale;
use App\Models\Concerns\CacheKeys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, CacheKeys;

    protected $fillable = [
        'name',
        'locale',
        'domain',
        'support_email',
        'auto_resolve_duration',
        'settings',
        'custom_attributes',
        'internal_attributes',
        'feature_flags',
        'limits',
        'status',
        'conversation_required_attributes',
    ];

    protected $casts = [
        'settings' => 'array',
        'custom_attributes' => 'array',
        'internal_attributes' => 'array',
        'feature_flags' => 'integer',
        'limits' => 'array',
        'status' => AccountStatus::class,
        'conversation_required_attributes' => 'array',
        'locale' => Locale::class,
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
            ->withPivot('role', 'availability', 'active_at')
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
     * Get all integration hooks for the account.
     */
    public function integrationHooks(): HasMany
    {
        return $this->hasMany(\App\Models\Integration\Hook::class);
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
     * Get all custom roles for the account.
     */
    public function customRoles(): HasMany
    {
        return $this->hasMany(CustomRole::class);
    }

    /**
     * Get all reporting events for the account.
     */
    public function reportingEvents(): HasMany
    {
        return $this->hasMany(ReportingEvent::class);
    }

    /**
     * Check if a feature is enabled for the account.
     * 
     * Uses features.yml naming convention directly for simplicity.
     */
    public function feature_enabled(string $feature): bool
    {
        $flagMap = $this->getFeatureFlagMap();
        
        if (isset($flagMap[$feature])) {
            return ($this->feature_flags & $flagMap[$feature]) !== 0;
        }
        
        // Default feature availability for unknown features
        $defaultFeatures = [
            // Enterprise features (disabled by default)
            'saml' => false,
            'sla' => false,
            'custom_roles' => false,
            'channel_voice' => false,
            'advanced_search' => false,
            'companies' => false,
            'audit_logs' => false,
            'response_bot' => false,
            'message_reply_to' => false,
            'insert_article_in_reply' => false,
            'inbox_view' => false,
            'help_center_embedding_search' => false,
            'captain_integration' => false,
            'chatwoot_v4' => true,
            'report_v4' => false,
            'contact_chatwoot_support_team' => false,
            // Legacy/deprecated features
            'mobile_v2' => false,
            'whatsapp_embedded_signup' => false,
            'whatsapp_campaign' => false,
            'crm_v2' => false,
            'assignment_v2' => true,
            'twilio_content_templates' => false,
            'advanced_search_indexing' => false,
            'reply_mailer_migration' => false,
            'quoted_email_reply' => false,
        ];
        
        // Check if enterprise feature is enabled in custom_attributes
        // Enterprise features that are too complex for bit flags or stored separately
        $enterpriseFeatures = [
            'saml', 'sla', 'custom_roles', 'audit_logs',
            'advanced_search', 'companies'
        ];
        
        if (in_array($feature, $enterpriseFeatures)) {
            $customAttributes = $this->custom_attributes ?? [];
            $enabledFeatures = $customAttributes['enabled_enterprise_features'] ?? [];
            return in_array($feature, $enabledFeatures);
        }
        
        return $defaultFeatures[$feature] ?? false;
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
        return $query->where('status', AccountStatus::ACTIVE);
    }

    /**
     * Check if account is active.
     */
    public function getActiveAttribute(): bool
    {
        return $this->status === AccountStatus::ACTIVE;
    }

    /**
     * Check if account is suspended.
     */
    public function getSuspendedAttribute(): bool
    {
        return $this->status === AccountStatus::SUSPENDED;
    }

    /**
     * Check if a feature is enabled.
     */
    public function featureEnabled(string $feature): bool
    {
        return $this->feature_enabled($feature);
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
     * Get the feature flag map using features.yml naming convention.
     * This centralizes the mapping to avoid duplication.
     * 
     * @return array<string, int>
     */
    public function getFeatureFlagMap(): array
    {
        return [
            // Communication Channels - bits 1-8
            'inbound_emails' => 1,
            'channel_email' => 2,
            'channel_facebook' => 4,
            'channel_twitter' => 8,
            'channel_website' => 16,
            'channel_instagram' => 32,
            'channel_voice' => 64,
            'channel_tiktok' => 128,
            
            // Product Features - bits 9-16
            'help_center' => 256,
            'agent_bots' => 512,
            'macros' => 1024,
            'agent_management' => 2048,
            'team_management' => 4096,
            'inbox_management' => 8192,
            'labels' => 16384,
            'custom_attributes' => 32768,
            
            // More Product Features - bits 17-24
            'automations' => 65536,
            'canned_responses' => 131072,
            'integrations' => 262144,
            'voice_recorder' => 524288,
            'campaigns' => 1048576,
            'reports' => 2097152,
            'crm' => 4194304,
            'auto_resolve_conversations' => 8388608,
            
            // Additional Features - bits 25-32
            'custom_reply_email' => 16777216,
            'custom_reply_domain' => 33554432,
            'ip_lookup' => 67108864,
            'quoted_email_reply' => 134217728,
            'linear_integration' => 268435456,
            'shopify_integration' => 536870912,
            'crm_integration' => 1073741824,
            'notion_integration' => 2147483648,
            
            // Enterprise Features - bits 33-40 (use custom_attributes instead)
            // These are handled separately in custom_attributes['enabled_enterprise_features']
            // 'disable_branding', 'audit_logs', 'sla', 'custom_roles', 'saml', 'advanced_search', 'companies'
            
            // Internal/System Features - sharing bits with related features
            'email_continuity_on_api_channel' => 2, // shares with channel_email
            'mobile_v2' => 16, // shares with channel_website  
            'chatwoot_v4' => 1048576, // shares with campaigns
            'report_v4' => 2097152, // shares with reports
            'contact_chatwoot_support_team' => 262144, // shares with integrations
            'search_with_gin' => 2097152, // shares with reports
            'advanced_search_indexing' => 2097152, // shares with reports
            'whatsapp_embedded_signup' => 4, // shares with channel_facebook
            'whatsapp_campaign' => 1048576, // shares with campaigns
            'crm_v2' => 4194304, // shares with crm
            'assignment_v2' => 4096, // shares with team_management
            'twilio_content_templates' => 4, // shares with channel_facebook
            'reply_mailer_migration' => 2, // shares with channel_email
            'inbox_view' => 8192, // shares with inbox_management
            'help_center_embedding_search' => 256, // shares with help_center
            'captain_integration' => 512, // shares with agent_bots
            'captain_integration_v2' => 512, // shares with agent_bots
            'response_bot' => 512, // shares with agent_bots
            'message_reply_to' => 2, // shares with channel_email
            'insert_article_in_reply' => 256, // shares with help_center
        ];
    }

    /**
     * Get enterprise features that use custom_attributes instead of bit flags.
     * 
     * @return array<string>
     */
    private function getEnterpriseFeatures(): array
    {
        return [
            'saml', 'sla', 'custom_roles', 'audit_logs',
            'advanced_search', 'companies'
        ];
    }

    /**
     * Enable a feature for this account.
     */
    public function enableFeature(string $feature): bool
    {
        $flagMap = $this->getFeatureFlagMap();
        
        
        if (isset($flagMap[$feature])) {
            $this->feature_flags |= $flagMap[$feature];
            $this->save();
            return true;
        }
        
        // Handle enterprise features that don't use bit flags
        $enterpriseFeatures = $this->getEnterpriseFeatures();
        
        if (in_array($feature, $enterpriseFeatures)) {
            // Store enterprise features in custom_attributes
            $customAttributes = $this->custom_attributes ?? [];
            $customAttributes['enabled_enterprise_features'] = $customAttributes['enabled_enterprise_features'] ?? [];
            
            if (!in_array($feature, $customAttributes['enabled_enterprise_features'])) {
                $customAttributes['enabled_enterprise_features'][] = $feature;
                $this->custom_attributes = $customAttributes;
                $this->save();
            }
            return true;
        }
        
        return false;
    }

    /**
     * Disable a feature for this account.
     */
    public function disableFeature(string $feature): bool
    {
        $flagMap = $this->getFeatureFlagMap();
        
        if (isset($flagMap[$feature])) {
            $this->feature_flags &= ~$flagMap[$feature];
            $this->save();
            return true;
        }
        
        // Handle enterprise features that don't use bit flags
        $enterpriseFeatures = $this->getEnterpriseFeatures();
        
        if (in_array($feature, $enterpriseFeatures)) {
            // Remove enterprise features from custom_attributes
            $customAttributes = $this->custom_attributes ?? [];
            $enabledFeatures = $customAttributes['enabled_enterprise_features'] ?? [];
            
            $enabledFeatures = array_filter($enabledFeatures, function($f) use ($feature) {
                return $f !== $feature;
            });
            
            $customAttributes['enabled_enterprise_features'] = array_values($enabledFeatures);
            $this->custom_attributes = $customAttributes;
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Get all enabled features for this account.
     * Returns feature names using features.yml naming convention.
     */
    public function getEnabledFeatures(): array
    {
        $flagMap = $this->getFeatureFlagMap();
        
        $enabledFeatures = [];
        foreach ($flagMap as $feature => $flag) {
            if (($this->feature_flags & $flag) !== 0) {
                // Only add unique features (since some share bits)
                if (!in_array($feature, $enabledFeatures)) {
                    $enabledFeatures[] = $feature;
                }
            }
        }
        
        // Add enterprise features from custom_attributes
        $customAttributes = $this->custom_attributes ?? [];
        $enterpriseFeatures = $customAttributes['enabled_enterprise_features'] ?? [];
        $enabledFeatures = array_merge($enabledFeatures, $enterpriseFeatures);
        
        return $enabledFeatures;
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

    /**
     * Get the locale attribute as a string code (e.g., 'en')
     * This allows backwards compatibility with code expecting locale as string
     */
    public function getLocaleCodeAttribute(): string
    {
        return $this->locale?->getCode() ?? 'en';
    }

    /**
     * Set locale from string code, enum, or integer
     * 
     * Converts various locale representations to integer for database storage:
     * - string: Locale code (e.g., 'en', 'fr') - most common use case
     * - Locale: Enum instance (e.g., Locale::EN) - direct enum assignment
     * - int: Raw integer value (e.g., 0, 3) - internal use, avoid in application code
     * 
     * @param string|Locale|int $value The locale value in any supported format
     * @return void
     * @throws \InvalidArgumentException If string code is invalid
     * @throws \ValueError If integer value doesn't correspond to a valid locale
     */
    public function setLocaleAttribute(string|Locale|int $value): void
    {
        if ($value instanceof Locale) {
            $this->attributes['locale'] = $value->value;
        } elseif (is_string($value)) {
            $this->attributes['locale'] = Locale::fromCode($value)->value;
        } else {
            // Validate that integer corresponds to a valid enum value
            // Locale::from() will throw ValueError if invalid
            Locale::from($value);
            $this->attributes['locale'] = $value;
        }
    }
}
