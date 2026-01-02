<?php

namespace App\Services;

use App\Models\Account;
use App\Models\InstallationConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Feature Flag Service
 * 
 * Manages feature flags for accounts including default assignment,
 * premium feature filtering, and feature metadata management.
 */
class FeatureFlagService
{
    private const CACHE_PREFIX = 'feature_flags:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get feature flag definitions.
     */
    public static function getFeatureDefinitions(): array
    {
        return [
            'shopify_integration' => [
                'display_name' => 'Shopify Integration',
                'description' => 'Enable Shopify e-commerce integration',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/shopify',
            ],
            'custom_roles' => [
                'display_name' => 'Custom Roles',
                'description' => 'Create and manage custom user roles',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/user-management/custom-roles',
            ],
            'sla_policies' => [
                'display_name' => 'SLA Policies',
                'description' => 'Service Level Agreement tracking and enforcement',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/sla-policies',
            ],
            'linear_integration' => [
                'display_name' => 'Linear Integration',
                'description' => 'Integrate with Linear for issue tracking',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/linear',
            ],
            'slack_integration' => [
                'display_name' => 'Slack Integration',
                'description' => 'Connect with Slack for team notifications',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/slack',
            ],
            'openai_integration' => [
                'display_name' => 'OpenAI Integration',
                'description' => 'AI-powered conversation assistance',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/integrations/openai',
            ],
            'audit_logs' => [
                'display_name' => 'Audit Logs',
                'description' => 'Comprehensive activity logging and monitoring',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/audit-logs',
            ],
            'advanced_reporting' => [
                'display_name' => 'Advanced Reporting',
                'description' => 'Detailed analytics and custom reports',
                'enabled' => false,
                'premium' => true,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/reporting',
            ],
            'team_management' => [
                'display_name' => 'Team Management',
                'description' => 'Organize agents into teams',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/user-management/teams',
            ],
            'automation_rules' => [
                'display_name' => 'Automation Rules',
                'description' => 'Automate conversation workflows',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/automation',
            ],
            'csat_surveys' => [
                'display_name' => 'CSAT Surveys',
                'description' => 'Customer satisfaction surveys',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/csat',
            ],
            'campaigns' => [
                'display_name' => 'Campaigns',
                'description' => 'Proactive messaging campaigns',
                'enabled' => true,
                'premium' => false,
                'chatwoot_internal' => false,
                'help_url' => 'https://docs.chatwoot.com/features/campaigns',
            ],
        ];
    }

    /**
     * Load feature defaults and create ACCOUNT_LEVEL_FEATURE_DEFAULTS config.
     */
    public static function loadFeatureDefaults(): void
    {
        $features = self::getFeatureDefinitions();
        $defaultFeatures = [];

        foreach ($features as $key => $feature) {
            if ($feature['enabled'] && !$feature['premium']) {
                $defaultFeatures[] = $key;
            }
        }

        // Store as configuration
        InstallationConfig::setConfig(
            'ACCOUNT_LEVEL_FEATURE_DEFAULTS',
            $defaultFeatures,
            false
        );

        Log::info('Feature defaults loaded', [
            'default_features' => $defaultFeatures
        ]);
    }

    /**
     * Assign default features to a new account.
     */
    public static function assignFeaturesToAccount(Account $account): void
    {
        $defaultFeatures = InstallationConfig::getConfig('ACCOUNT_LEVEL_FEATURE_DEFAULTS', []);
        
        if (empty($defaultFeatures)) {
            // Load defaults if not set
            self::loadFeatureDefaults();
            $defaultFeatures = InstallationConfig::getConfig('ACCOUNT_LEVEL_FEATURE_DEFAULTS', []);
        }

        // Get current account features
        $currentFeatures = $account->features ?? [];
        
        // Merge with defaults (don't override existing)
        $newFeatures = array_unique(array_merge($currentFeatures, $defaultFeatures));
        
        // Update account
        $account->features = $newFeatures;
        $account->save();

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . 'account:' . $account->id);

        Log::info('Features assigned to account', [
            'account_id' => $account->id,
            'features' => $newFeatures
        ]);
    }

    /**
     * Check if account has a specific feature enabled.
     */
    public static function isFeatureEnabled(Account $account, string $feature): bool
    {
        $cacheKey = self::CACHE_PREFIX . 'account:' . $account->id . ':' . $feature;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($account, $feature) {
            $accountFeatures = $account->features ?? [];
            return in_array($feature, $accountFeatures);
        });
    }

    /**
     * Enable a feature for an account.
     */
    public static function enableFeature(Account $account, string $feature): bool
    {
        $features = self::getFeatureDefinitions();
        
        if (!isset($features[$feature])) {
            return false;
        }

        $featureConfig = $features[$feature];
        
        // Check if premium feature and account has premium access
        if ($featureConfig['premium'] && !$account->isPremium()) {
            return false;
        }

        $currentFeatures = $account->features ?? [];
        
        if (!in_array($feature, $currentFeatures)) {
            $currentFeatures[] = $feature;
            $account->features = $currentFeatures;
            $account->save();
            
            // Clear cache
            Cache::forget(self::CACHE_PREFIX . 'account:' . $account->id);
        }

        return true;
    }

    /**
     * Disable a feature for an account.
     */
    public static function disableFeature(Account $account, string $feature): bool
    {
        $currentFeatures = $account->features ?? [];
        
        if (in_array($feature, $currentFeatures)) {
            $currentFeatures = array_values(array_diff($currentFeatures, [$feature]));
            $account->features = $currentFeatures;
            $account->save();
            
            // Clear cache
            Cache::forget(self::CACHE_PREFIX . 'account:' . $account->id);
        }

        return true;
    }

    /**
     * Get feature metadata for an account.
     */
    public static function getFeatureMetadata(string $feature): ?array
    {
        $features = self::getFeatureDefinitions();
        return $features[$feature] ?? null;
    }

    /**
     * Get all features available to an account.
     */
    public static function getAvailableFeatures(Account $account): array
    {
        $features = self::getFeatureDefinitions();
        $availableFeatures = [];

        foreach ($features as $key => $feature) {
            // Skip premium features for non-premium accounts
            if ($feature['premium'] && !$account->isPremium()) {
                continue;
            }

            // Skip internal features
            if ($feature['chatwoot_internal']) {
                continue;
            }

            $availableFeatures[$key] = array_merge($feature, [
                'enabled_for_account' => self::isFeatureEnabled($account, $key)
            ]);
        }

        return $availableFeatures;
    }

    /**
     * Get enabled features for an account with metadata.
     */
    public static function getEnabledFeatures(Account $account): array
    {
        $accountFeatures = $account->features ?? [];
        $features = self::getFeatureDefinitions();
        $enabledFeatures = [];

        foreach ($accountFeatures as $featureKey) {
            if (isset($features[$featureKey])) {
                $enabledFeatures[$featureKey] = $features[$featureKey];
            }
        }

        return $enabledFeatures;
    }

    /**
     * Bulk update account features.
     */
    public static function updateAccountFeatures(Account $account, array $features): array
    {
        $featureDefinitions = self::getFeatureDefinitions();
        $validFeatures = [];
        $errors = [];

        foreach ($features as $feature) {
            if (!isset($featureDefinitions[$feature])) {
                $errors[] = "Unknown feature: {$feature}";
                continue;
            }

            $featureConfig = $featureDefinitions[$feature];
            
            // Check premium access
            if ($featureConfig['premium'] && !$account->isPremium()) {
                $errors[] = "Premium feature not available: {$feature}";
                continue;
            }

            // Skip internal features
            if ($featureConfig['chatwoot_internal']) {
                $errors[] = "Internal feature not available: {$feature}";
                continue;
            }

            $validFeatures[] = $feature;
        }

        // Update account features
        $account->features = $validFeatures;
        $account->save();

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . 'account:' . $account->id);

        return [
            'success' => empty($errors),
            'features' => $validFeatures,
            'errors' => $errors,
        ];
    }

    /**
     * Clear feature cache for an account.
     */
    public static function clearAccountCache(Account $account): void
    {
        $pattern = self::CACHE_PREFIX . 'account:' . $account->id . '*';
        
        // This is a simplified cache clearing - in production you might want
        // to use Redis pattern matching or cache tags
        Cache::forget(self::CACHE_PREFIX . 'account:' . $account->id);
    }
}