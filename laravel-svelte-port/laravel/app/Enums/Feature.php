<?php

namespace App\Enums;

enum Feature: string
{
    // Premium Features
    case SHOPIFY_INTEGRATION = 'shopify_integration';
    case CUSTOM_ROLES = 'custom_roles';
    case SLA_POLICIES = 'sla_policies';
    case LINEAR_INTEGRATION = 'linear_integration';
    case OPENAI_INTEGRATION = 'openai_integration';
    case AUDIT_LOGS = 'audit_logs';
    case ADVANCED_REPORTING = 'advanced_reporting';
    case CUSTOM_BRANDING = 'custom_branding';
    case DISABLE_BRANDING = 'disable_branding';
    case AGENT_CAPACITY = 'agent_capacity';
    case SAML = 'saml';

    // Standard Features
    case SLACK_INTEGRATION = 'slack_integration';
    case TEAM_MANAGEMENT = 'team_management';
    case AUTOMATION_RULES = 'automation_rules';
    case CSAT_SURVEYS = 'csat_surveys';
    case CAMPAIGNS = 'campaigns';
    case WHATSAPP_INTEGRATION = 'whatsapp_integration';
    case FACEBOOK_INTEGRATION = 'facebook_integration';
    case INSTAGRAM_INTEGRATION = 'instagram_integration';
    case TWITTER_INTEGRATION = 'twitter_integration';
    case EMAIL_INTEGRATION = 'email_integration';
    case WEBSITE_WIDGET = 'website_widget';
    case MOBILE_APP = 'mobile_app';
    case API_ACCESS = 'api_access';
    case WEBHOOKS = 'webhooks';
    case MACROS = 'macros';
    case CANNED_RESPONSES = 'canned_responses';
    case LABELS = 'labels';
    case CONTACT_MANAGEMENT = 'contact_management';
    case CONVERSATION_ASSIGNMENT = 'conversation_assignment';
    case CONVERSATION_SEARCH = 'conversation_search';
    case FILE_ATTACHMENTS = 'file_attachments';
    case CONVERSATION_NOTES = 'conversation_notes';
    case AGENT_AVAILABILITY = 'agent_availability';
    case CONVERSATION_STATUS = 'conversation_status';
    case REAL_TIME_NOTIFICATIONS = 'real_time_notifications';

    /**
     * Get feature metadata.
     */
    public function metadata(): array
    {
        return match ($this) {
            self::SHOPIFY_INTEGRATION => [
                'display_name' => 'Shopify Integration',
                'description' => 'Enable Shopify e-commerce integration',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/shopify',
            ],
            self::CUSTOM_ROLES => [
                'display_name' => 'Custom Roles',
                'description' => 'Create and manage custom user roles',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/user-management/custom-roles',
            ],
            self::SLA_POLICIES => [
                'display_name' => 'SLA Policies',
                'description' => 'Service Level Agreement tracking and enforcement',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/sla-policies',
            ],
            self::LINEAR_INTEGRATION => [
                'display_name' => 'Linear Integration',
                'description' => 'Integrate with Linear for issue tracking',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/linear',
            ],
            self::CUSTOM_BRANDING => [
                'display_name' => 'Custom Branding',
                'description' => 'Apply your own branding to this installation',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/custom-branding',
            ],
            self::DISABLE_BRANDING => [
                'display_name' => 'Disable Branding',
                'description' => 'Disable branding on live-chat widget and external emails',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/disable-branding',
            ],
            self::AGENT_CAPACITY => [
                'display_name' => 'Agent Capacity',
                'description' => 'Set limits to auto-assigning conversations to your agents',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/agent-capacity',
            ],
            self::SAML => [
                'display_name' => 'SAML SSO',
                'description' => 'Configuration for controlling SAML Single Sign-On availability',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/saml-sso',
            ],
            self::SLACK_INTEGRATION => [
                'display_name' => 'Slack Integration',
                'description' => 'Connect with Slack for team notifications',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/slack',
            ],
            self::OPENAI_INTEGRATION => [
                'display_name' => 'OpenAI Integration',
                'description' => 'AI-powered conversation assistance',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/openai',
            ],
            self::AUDIT_LOGS => [
                'display_name' => 'Audit Logs',
                'description' => 'Comprehensive activity logging and monitoring',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/audit-logs',
            ],
            self::ADVANCED_REPORTING => [
                'display_name' => 'Advanced Reporting',
                'description' => 'Detailed analytics and custom reports',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/reporting',
            ],
            self::TEAM_MANAGEMENT => [
                'display_name' => 'Team Management',
                'description' => 'Organize agents into teams',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/user-management/teams',
            ],
            self::AUTOMATION_RULES => [
                'display_name' => 'Automation Rules',
                'description' => 'Automate conversation workflows',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/automation',
            ],
            self::CSAT_SURVEYS => [
                'display_name' => 'CSAT Surveys',
                'description' => 'Customer satisfaction surveys',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/csat',
            ],
            self::CAMPAIGNS => [
                'display_name' => 'Campaigns',
                'description' => 'Proactive messaging campaigns',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/campaigns',
            ],
            self::WHATSAPP_INTEGRATION => [
                'display_name' => 'WhatsApp Integration',
                'description' => 'Connect with WhatsApp Business API',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/whatsapp',
            ],
            self::FACEBOOK_INTEGRATION => [
                'display_name' => 'Facebook Integration',
                'description' => 'Connect with Facebook Messenger',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/facebook',
            ],
            self::INSTAGRAM_INTEGRATION => [
                'display_name' => 'Instagram Integration',
                'description' => 'Connect with Instagram Direct Messages',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/instagram',
            ],
            self::TWITTER_INTEGRATION => [
                'display_name' => 'Twitter Integration',
                'description' => 'Connect with Twitter Direct Messages',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/twitter',
            ],
            self::EMAIL_INTEGRATION => [
                'display_name' => 'Email Integration',
                'description' => 'Handle customer emails as conversations',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/email',
            ],
            self::WEBSITE_WIDGET => [
                'display_name' => 'Website Widget',
                'description' => 'Embeddable chat widget for websites',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/channels/website',
            ],
            self::MOBILE_APP => [
                'display_name' => 'Mobile App',
                'description' => 'Native mobile applications for iOS and Android',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/mobile-app',
            ],
            self::API_ACCESS => [
                'display_name' => 'API Access',
                'description' => 'RESTful API for integrations and automation',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/api',
            ],
            self::WEBHOOKS => [
                'display_name' => 'Webhooks',
                'description' => 'Real-time event notifications via HTTP callbacks',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/webhooks',
            ],
            self::MACROS => [
                'display_name' => 'Macros',
                'description' => 'Predefined actions for quick conversation handling',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/macros',
            ],
            self::CANNED_RESPONSES => [
                'display_name' => 'Canned Responses',
                'description' => 'Pre-written responses for common queries',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/canned-responses',
            ],
            self::LABELS => [
                'display_name' => 'Labels',
                'description' => 'Organize conversations with custom labels',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/labels',
            ],
            self::CONTACT_MANAGEMENT => [
                'display_name' => 'Contact Management',
                'description' => 'Comprehensive customer contact database',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/contacts',
            ],
            self::CONVERSATION_ASSIGNMENT => [
                'display_name' => 'Conversation Assignment',
                'description' => 'Assign conversations to specific agents or teams',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/assignment',
            ],
            self::CONVERSATION_SEARCH => [
                'display_name' => 'Conversation Search',
                'description' => 'Search through conversations and messages',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/search',
            ],
            self::FILE_ATTACHMENTS => [
                'display_name' => 'File Attachments',
                'description' => 'Send and receive file attachments in conversations',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/attachments',
            ],
            self::CONVERSATION_NOTES => [
                'display_name' => 'Conversation Notes',
                'description' => 'Internal notes for agent collaboration',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/notes',
            ],
            self::AGENT_AVAILABILITY => [
                'display_name' => 'Agent Availability',
                'description' => 'Manage agent online/offline status',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/availability',
            ],
            self::CONVERSATION_STATUS => [
                'display_name' => 'Conversation Status',
                'description' => 'Track conversation states (open, resolved, pending)',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/status',
            ],
            self::REAL_TIME_NOTIFICATIONS => [
                'display_name' => 'Real-time Notifications',
                'description' => 'Instant notifications for new messages and events',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/notifications',
            ],
        };
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
            ->map(fn($feature) => $feature->metadata())
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