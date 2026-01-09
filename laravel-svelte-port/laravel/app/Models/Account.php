<?php

namespace App\Models;

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
        'status' => 'integer',
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
     */
    public function feature_enabled(string $feature): bool
    {
        $flagMap = [
            // Core communication channels (bits 1-8)
            'email' => 1,
            'sms' => 2,
            'messenger' => 4,
            'telegram' => 8,
            'whatsapp' => 16,
            'tiktok' => 32,
            'instagram' => 64,
            'line' => 128,
            
            // Product features (bits 9-16)
            'macros' => 256,
            'labels' => 512,
            'teams' => 1024,
            'reports' => 2048,
            'campaigns' => 4096,
            'webhooks' => 8192,
            'google' => 16384,
            'microsoft' => 32768,
            
            // Integrations (bits 17-24)
            'linear' => 65536,
            'slack' => 131072,
            'shopify' => 262144,
            'cannedResponses' => 524288,
            'helpCenter' => 1048576,
            'automationRules' => 2097152,
            'customAttributes' => 4194304,
            'liveChat' => 8388608,
            
            // Enterprise features (bits 25-32)
            'assignment_v2' => 16777216,
            'inbox_assistant' => 33554432,
            'advanced_reporting' => 67108864,
            'crm_integration' => 134217728,
            'notion_integration' => 268435456,
            'custom_branding' => 536870912,
            'disable_branding' => 1073741824,
            'agent_capacity' => 2147483648,
            
            // Feature name mappings for Rails parity and YAML config
            'email_integration' => 1, // email
            'channel_email' => 1, // email
            'website_widget' => 8388608, // liveChat
            'channel_website' => 8388608, // liveChat
            'api_access' => 8192, // webhooks
            'team_management' => 1024, // teams
            'automation_rules' => 2097152, // automationRules
            'csat_surveys' => 2048, // reports
            'whatsapp_integration' => 16, // whatsapp
            'facebook_integration' => 4, // messenger
            'channel_facebook' => 4, // messenger
            'instagram_integration' => 64, // instagram
            'channel_instagram' => 64, // instagram
            'twitter_integration' => 2, // sms (reuse for social)
            'channel_twitter' => 2, // sms (reuse for social)
            'canned_responses' => 524288, // cannedResponses
            'contact_management' => 4194304, // customAttributes
            'conversation_assignment' => 16777216, // assignment_v2
            'conversation_search' => 2048, // reports
            'file_attachments' => 8388608, // liveChat
            'conversation_notes' => 4194304, // customAttributes
            'agent_availability' => 1024, // teams
            'conversation_status' => 2097152, // automationRules
            'real_time_notifications' => 8192, // webhooks
            'mobile_app' => 8388608, // liveChat
            'slack_integration' => 131072, // slack
            'linear_integration' => 65536, // linear
            'shopify_integration' => 262144, // shopify
            'openai_integration' => 33554432, // inbox_assistant
            'agent_bots' => 1048576, // helpCenter (reuse)
            'integrations' => 8192, // webhooks (reuse)
            'crm' => 4194304, // customAttributes (reuse)
            'voice_recorder' => 8388608, // liveChat (reuse)
            'inbound_emails' => 1, // email (reuse)
            'ip_lookup' => 2048, // reports (reuse)
            'auto_resolve_conversations' => 2097152, // automationRules (reuse)
            'custom_reply_email' => 1, // email (reuse)
            'custom_reply_domain' => 1, // email (reuse)
        ];
        
        if (isset($flagMap[$feature])) {
            return ($this->feature_flags & $flagMap[$feature]) !== 0;
        }
        
        // Default feature availability for unknown features (Rails compatibility)
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
        return $query->where('status', 0);
    }

    /**
     * Check if account is active.
     */
    public function getActiveAttribute(): bool
    {
        return $this->status === 0;
    }

    /**
     * Check if account is suspended.
     */
    public function getSuspendedAttribute(): bool
    {
        return $this->status === 1;
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
     * Enable a feature for this account.
     */
    public function enableFeature(string $feature): bool
    {
        $flagMap = [
            // Core communication channels (bits 1-8)
            'email' => 1,
            'sms' => 2,
            'messenger' => 4,
            'telegram' => 8,
            'whatsapp' => 16,
            'tiktok' => 32,
            'instagram' => 64,
            'line' => 128,
            
            // Product features (bits 9-16)
            'macros' => 256,
            'labels' => 512,
            'teams' => 1024,
            'reports' => 2048,
            'campaigns' => 4096,
            'webhooks' => 8192,
            'google' => 16384,
            'microsoft' => 32768,
            
            // Integrations (bits 17-24)
            'linear' => 65536,
            'slack' => 131072,
            'shopify' => 262144,
            'cannedResponses' => 524288,
            'helpCenter' => 1048576,
            'automationRules' => 2097152,
            'customAttributes' => 4194304,
            'liveChat' => 8388608,
            
            // Enterprise features (bits 25-32)
            'assignment_v2' => 16777216,
            'inbox_assistant' => 33554432,
            'advanced_reporting' => 67108864,
            'crm_integration' => 134217728,
            'notion_integration' => 268435456,
            'custom_branding' => 536870912,
            'disable_branding' => 1073741824,
            'agent_capacity' => 2147483648,
            
            // Feature name mappings for Rails parity and YAML config
            'email_integration' => 1, // email
            'channel_email' => 1, // email
            'website_widget' => 8388608, // liveChat
            'channel_website' => 8388608, // liveChat
            'api_access' => 8192, // webhooks
            'team_management' => 1024, // teams
            'automation_rules' => 2097152, // automationRules
            'csat_surveys' => 2048, // reports
            'whatsapp_integration' => 16, // whatsapp
            'facebook_integration' => 4, // messenger
            'channel_facebook' => 4, // messenger
            'instagram_integration' => 64, // instagram
            'channel_instagram' => 64, // instagram
            'twitter_integration' => 2, // sms (reuse for social)
            'channel_twitter' => 2, // sms (reuse for social)
            'canned_responses' => 524288, // cannedResponses
            'contact_management' => 4194304, // customAttributes
            'conversation_assignment' => 16777216, // assignment_v2
            'conversation_search' => 2048, // reports
            'file_attachments' => 8388608, // liveChat
            'conversation_notes' => 4194304, // customAttributes
            'agent_availability' => 1024, // teams
            'conversation_status' => 2097152, // automationRules
            'real_time_notifications' => 8192, // webhooks
            'mobile_app' => 8388608, // liveChat
            'slack_integration' => 131072, // slack
            'linear_integration' => 65536, // linear
            'shopify_integration' => 262144, // shopify
            'openai_integration' => 33554432, // inbox_assistant
            'agent_bots' => 1048576, // helpCenter (reuse)
            'integrations' => 8192, // webhooks (reuse)
            'crm' => 4194304, // customAttributes (reuse)
            'voice_recorder' => 8388608, // liveChat (reuse)
            'inbound_emails' => 1, // email (reuse)
            'ip_lookup' => 2048, // reports (reuse)
            'auto_resolve_conversations' => 2097152, // automationRules (reuse)
            'custom_reply_email' => 1, // email (reuse)
            'custom_reply_domain' => 1, // email (reuse)
        ];
        
        if (isset($flagMap[$feature])) {
            $this->feature_flags |= $flagMap[$feature];
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Disable a feature for this account.
     */
    public function disableFeature(string $feature): bool
    {
        $flagMap = [
            // Core communication channels (bits 1-8)
            'email' => 1,
            'sms' => 2,
            'messenger' => 4,
            'telegram' => 8,
            'whatsapp' => 16,
            'tiktok' => 32,
            'instagram' => 64,
            'line' => 128,
            
            // Product features (bits 9-16)
            'macros' => 256,
            'labels' => 512,
            'teams' => 1024,
            'reports' => 2048,
            'campaigns' => 4096,
            'webhooks' => 8192,
            'google' => 16384,
            'microsoft' => 32768,
            
            // Integrations (bits 17-24)
            'linear' => 65536,
            'slack' => 131072,
            'shopify' => 262144,
            'cannedResponses' => 524288,
            'helpCenter' => 1048576,
            'automationRules' => 2097152,
            'customAttributes' => 4194304,
            'liveChat' => 8388608,
            
            // Enterprise features (bits 25-32)
            'assignment_v2' => 16777216,
            'inbox_assistant' => 33554432,
            'advanced_reporting' => 67108864,
            'crm_integration' => 134217728,
            'notion_integration' => 268435456,
            'custom_branding' => 536870912,
            'disable_branding' => 1073741824,
            'agent_capacity' => 2147483648,
            
            // Feature name mappings for Rails parity and YAML config
            'email_integration' => 1, // email
            'channel_email' => 1, // email
            'website_widget' => 8388608, // liveChat
            'channel_website' => 8388608, // liveChat
            'api_access' => 8192, // webhooks
            'team_management' => 1024, // teams
            'automation_rules' => 2097152, // automationRules
            'csat_surveys' => 2048, // reports
            'whatsapp_integration' => 16, // whatsapp
            'facebook_integration' => 4, // messenger
            'channel_facebook' => 4, // messenger
            'instagram_integration' => 64, // instagram
            'channel_instagram' => 64, // instagram
            'twitter_integration' => 2, // sms (reuse for social)
            'channel_twitter' => 2, // sms (reuse for social)
            'canned_responses' => 524288, // cannedResponses
            'contact_management' => 4194304, // customAttributes
            'conversation_assignment' => 16777216, // assignment_v2
            'conversation_search' => 2048, // reports
            'file_attachments' => 8388608, // liveChat
            'conversation_notes' => 4194304, // customAttributes
            'agent_availability' => 1024, // teams
            'conversation_status' => 2097152, // automationRules
            'real_time_notifications' => 8192, // webhooks
            'mobile_app' => 8388608, // liveChat
            'slack_integration' => 131072, // slack
            'linear_integration' => 65536, // linear
            'shopify_integration' => 262144, // shopify
            'openai_integration' => 33554432, // inbox_assistant
            'agent_bots' => 1048576, // helpCenter (reuse)
            'integrations' => 8192, // webhooks (reuse)
            'crm' => 4194304, // customAttributes (reuse)
            'voice_recorder' => 8388608, // liveChat (reuse)
            'inbound_emails' => 1, // email (reuse)
            'ip_lookup' => 2048, // reports (reuse)
            'auto_resolve_conversations' => 2097152, // automationRules (reuse)
            'custom_reply_email' => 1, // email (reuse)
            'custom_reply_domain' => 1, // email (reuse)
        ];
        
        if (isset($flagMap[$feature])) {
            $this->feature_flags &= ~$flagMap[$feature];
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Get all enabled features for this account.
     */
    public function getEnabledFeatures(): array
    {
        $flagMap = [
            // Core communication channels (bits 1-8)
            'email' => 1,
            'sms' => 2,
            'messenger' => 4,
            'telegram' => 8,
            'whatsapp' => 16,
            'tiktok' => 32,
            'instagram' => 64,
            'line' => 128,
            
            // Product features (bits 9-16)
            'macros' => 256,
            'labels' => 512,
            'teams' => 1024,
            'reports' => 2048,
            'campaigns' => 4096,
            'webhooks' => 8192,
            'google' => 16384,
            'microsoft' => 32768,
            
            // Integrations (bits 17-24)
            'linear' => 65536,
            'slack' => 131072,
            'shopify' => 262144,
            'cannedResponses' => 524288,
            'helpCenter' => 1048576,
            'automationRules' => 2097152,
            'customAttributes' => 4194304,
            'liveChat' => 8388608,
            
            // Enterprise features (bits 25-32)
            'assignment_v2' => 16777216,
            'inbox_assistant' => 33554432,
            'advanced_reporting' => 67108864,
            'crm_integration' => 134217728,
            'notion_integration' => 268435456,
            'custom_branding' => 536870912,
            'disable_branding' => 1073741824,
            'agent_capacity' => 2147483648,
        ];
        
        $enabledFeatures = [];
        foreach ($flagMap as $feature => $flag) {
            if (($this->feature_flags & $flag) !== 0) {
                $enabledFeatures[] = $feature;
            }
        }
        
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
