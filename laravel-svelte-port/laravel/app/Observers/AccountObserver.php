<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\FeatureConfigService;
use Illuminate\Support\Facades\Log;

class AccountObserver
{
    /**
     * Handle the Account "creating" event.
     * Initialize default features before saving to database.
     */
    public function creating(Account $account): void
    {
        // Initialize feature flags if not already set
        if ($account->feature_flags === null || $account->feature_flags === 0) {
            $this->enableDefaultFeatures($account);
        }
    }

    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        Log::info('Account created', [
            'account_id' => $account->id,
            'account_name' => $account->name,
            'enabled_features' => $account->getEnabledFeatures(),
        ]);
    }

    /**
     * Enable default features for the account based on configuration.
     */
    private function enableDefaultFeatures(Account $account): void
    {
        try {
            // Use the new FeatureConfigService
            $featureService = app(FeatureConfigService::class);
            $enabledFeatures = $featureService->getEnabledByDefault();
            
            // Extract feature names from metadata array
            $featureNames = collect($enabledFeatures)
                ->pluck('name')
                ->toArray();
            
            if (!empty($featureNames)) {
                $this->enableFeatures($account, $featureNames);
                
                Log::info('Default features enabled for account', [
                    'account_id' => $account->id ?? 'new',
                    'features_enabled' => $featureNames,
                ]);
            } else {
                $this->enableBasicFeatures($account);
            }

        } catch (\Exception $e) {
            Log::error('Failed to enable default features for account', [
                'account_id' => $account->id ?? 'new',
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to basic features
            $this->enableBasicFeatures($account);
        }
    }

    /**
     * Enable specific features for the account.
     */
    private function enableFeatures(Account $account, array $featureNames): void
    {
        $flagMap = $this->getFeatureFlagMap();
        
        foreach ($featureNames as $featureName) {
            if (isset($flagMap[$featureName])) {
                $account->feature_flags |= $flagMap[$featureName];
            }
        }
    }

    /**
     * Enable basic features as fallback.
     */
    private function enableBasicFeatures(Account $account): void
    {
        $basicFeatures = [
            'email_integration',
            'website_widget',
            'api_access',
            'webhooks',
            'macros',
            'canned_responses',
            'labels',
            'contact_management',
            'conversation_assignment',
            'conversation_search',
            'file_attachments',
            'conversation_notes',
            'agent_availability',
            'conversation_status',
            'real_time_notifications',
            'team_management',
            'automation_rules',
            'csat_surveys',
            'campaigns',
        ];

        $this->enableFeatures($account, $basicFeatures);
        
        Log::info('Basic features enabled as fallback', [
            'account_id' => $account->id ?? 'new',
            'features_enabled' => $basicFeatures,
        ]);
    }

    /**
     * Get the feature flag mapping (matches Account model).
     */
    private function getFeatureFlagMap(): array
    {
        return [
            'email' => 1,
            'sms' => 2,
            'messenger' => 4,
            'telegram' => 8,
            'whatsapp' => 16,
            'tiktok' => 32,
            'instagram' => 64,
            'line' => 128,
            'macros' => 256,
            'labels' => 512,
            'teams' => 1024,
            'reports' => 2048,
            'campaigns' => 4096,
            'webhooks' => 8192,
            'google' => 16384,
            'microsoft' => 32768,
            'linear' => 65536,
            'slack' => 131072,
            'shopify' => 262144,
            'cannedResponses' => 524288,
            'helpCenter' => 1048576,
            'automationRules' => 2097152,
            'customAttributes' => 4194304,
            'liveChat' => 8388608,
            // Enterprise features
            'assignment_v2' => 16777216,
            'inbox_assistant' => 33554432,
            'advanced_reporting' => 67108864,
            'crm_integration' => 134217728,
            'notion_integration' => 268435456,
            // Map YAML feature names to flag names
            'email_integration' => 1,
            'website_widget' => 8388608, // liveChat
            'api_access' => 8192, // webhooks
            'team_management' => 1024, // teams
            'automation_rules' => 2097152, // automationRules
            'csat_surveys' => 2048, // reports
            'whatsapp_integration' => 16,
            'facebook_integration' => 4, // messenger
            'instagram_integration' => 64,
            'twitter_integration' => 2, // sms (reuse for social)
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
            'audit_logs' => 67108864, // advanced_reporting
            'advanced_reporting' => 67108864,
            'custom_roles' => 134217728, // crm_integration (reuse)
            'sla_policies' => 268435456, // notion_integration (reuse)
        ];
    }
}