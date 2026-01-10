<?php

namespace App\Enums;

/**
 * Feature Enum
 * 
 * This enum loads features from the Rails config/features.yml file
 * to maintain complete parity between Rails and Laravel systems.
 */
enum Feature: string
{
    // Communication Channels
    case INBOUND_EMAILS = 'inbound_emails';
    case CHANNEL_EMAIL = 'channel_email';
    case CHANNEL_FACEBOOK = 'channel_facebook';
    case CHANNEL_TWITTER = 'channel_twitter';
    case CHANNEL_WEBSITE = 'channel_website';
    case CHANNEL_INSTAGRAM = 'channel_instagram';
    case CHANNEL_VOICE = 'channel_voice';
    case CHANNEL_TIKTOK = 'channel_tiktok';
    
    // Product Features
    case HELP_CENTER = 'help_center';
    case AGENT_BOTS = 'agent_bots';
    case MACROS = 'macros';
    case AGENT_MANAGEMENT = 'agent_management';
    case TEAM_MANAGEMENT = 'team_management';
    case INBOX_MANAGEMENT = 'inbox_management';
    case LABELS = 'labels';
    case CUSTOM_ATTRIBUTES = 'custom_attributes';
    case AUTOMATIONS = 'automations';
    case CANNED_RESPONSES = 'canned_responses';
    case INTEGRATIONS = 'integrations';
    case VOICE_RECORDER = 'voice_recorder';
    case CAMPAIGNS = 'campaigns';
    case REPORTS = 'reports';
    case CRM = 'crm';
    case AUTO_RESOLVE_CONVERSATIONS = 'auto_resolve_conversations';
    case CUSTOM_REPLY_EMAIL = 'custom_reply_email';
    case CUSTOM_REPLY_DOMAIN = 'custom_reply_domain';
    case IP_LOOKUP = 'ip_lookup';
    case QUOTED_EMAIL_REPLY = 'quoted_email_reply';
    
    // Integrations
    case LINEAR_INTEGRATION = 'linear_integration';
    case SHOPIFY_INTEGRATION = 'shopify_integration';
    case CRM_INTEGRATION = 'crm_integration';
    case NOTION_INTEGRATION = 'notion_integration';
    
    // Enterprise Features (Premium)
    case DISABLE_BRANDING = 'disable_branding';
    case AUDIT_LOGS = 'audit_logs';
    case SLA = 'sla';
    case CUSTOM_ROLES = 'custom_roles';
    case SAML = 'saml';
    case ADVANCED_SEARCH = 'advanced_search';
    case COMPANIES = 'companies';
    
    // Internal/System Features
    case EMAIL_CONTINUITY_ON_API_CHANNEL = 'email_continuity_on_api_channel';
    case MOBILE_V2 = 'mobile_v2';
    case CHATWOOT_V4 = 'chatwoot_v4';
    case REPORT_V4 = 'report_v4';
    case CONTACT_CHATWOOT_SUPPORT_TEAM = 'contact_chatwoot_support_team';
    case SEARCH_WITH_GIN = 'search_with_gin';
    case ADVANCED_SEARCH_INDEXING = 'advanced_search_indexing';
    case WHATSAPP_EMBEDDED_SIGNUP = 'whatsapp_embedded_signup';
    case WHATSAPP_CAMPAIGN = 'whatsapp_campaign';
    case CRM_V2 = 'crm_v2';
    case ASSIGNMENT_V2 = 'assignment_v2';
    case TWILIO_CONTENT_TEMPLATES = 'twilio_content_templates';
    case REPLY_MAILER_MIGRATION = 'reply_mailer_migration';
    case INBOX_VIEW = 'inbox_view';
    case HELP_CENTER_EMBEDDING_SEARCH = 'help_center_embedding_search';
    case CAPTAIN_INTEGRATION = 'captain_integration';
    case CAPTAIN_INTEGRATION_V2 = 'captain_integration_v2';
    case RESPONSE_BOT = 'response_bot';
    case MESSAGE_REPLY_TO = 'message_reply_to';
    case INSERT_ARTICLE_IN_REPLY = 'insert_article_in_reply';

    /**
     * Get feature metadata from Laravel config.
     */
    public function metadata(): array
    {
        $features = config('features.features', []);
        
        // Find this feature in Laravel config
        $feature = collect($features)->firstWhere('name', $this->value);
        
        if ($feature) {
            return [
                'display_name' => $feature['display_name'] ?? ucwords(str_replace('_', ' ', $this->value)),
                'enabled' => $feature['enabled'] ?? false,
                'premium' => $feature['premium'] ?? false,
                'chatwoot_internal' => $feature['chatwoot_internal'] ?? false,
                'deprecated' => $feature['deprecated'] ?? false,
                'help_url' => $feature['help_url'] ?? null,
            ];
        }
        
        // Fallback metadata if not found in config
        return [
            'display_name' => ucwords(str_replace('_', ' ', $this->value)),
            'enabled' => false,
            'premium' => in_array($this->value, config('features.premium_features', [])),
            'chatwoot_internal' => in_array($this->value, config('features.internal_features', [])),
            'deprecated' => in_array($this->value, config('features.deprecated_features', [])),
            'help_url' => null,
        ];
    }

    /**
     * Get all enabled features by default.
     */
    public static function getEnabledByDefault(): array
    {
        return collect(self::cases())
            ->filter(fn($feature) => $feature->metadata()['enabled'])
            ->map(function ($feature) {
                $metadata = $feature->metadata();
                $metadata['name'] = $feature->value;
                return $metadata;
            })
            ->values()
            ->toArray();
    }

    /**
     * Get all premium features.
     */
    public static function getPremiumFeatures(): array
    {
        return collect(self::cases())
            ->filter(fn($feature) => $feature->metadata()['premium'])
            ->map(function ($feature) {
                $metadata = $feature->metadata();
                $metadata['name'] = $feature->value;
                return $metadata;
            })
            ->values()
            ->toArray();
    }

    /**
     * Get feature by name.
     */
    public static function fromName(string $name): ?self
    {
        return collect(self::cases())->first(fn($case) => $case->value === $name);
    }
}