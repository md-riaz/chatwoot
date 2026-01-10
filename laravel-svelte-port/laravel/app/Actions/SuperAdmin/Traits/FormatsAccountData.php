<?php

namespace App\Actions\SuperAdmin\Traits;

use App\Data\SuperAdmin\AccountData;
use App\Data\SuperAdmin\AccountListData;
use App\Enums\AccountStatus;
use App\Models\Account;

trait FormatsAccountData
{
    /**
     * Format an Account model to AccountData DTO (for detail view with account_users)
     */
    protected function formatAccount(Account $account): AccountData
    {
        return new AccountData(
            id: $account->id,
            name: $account->name,
            locale: $this->formatLocale($account->locale),
            domain: $account->domain,
            support_email: $account->support_email,
            auto_resolve_duration: $account->auto_resolve_duration,
            settings: $account->settings,
            limits: $this->formatLimits($account),
            custom_attributes: $account->custom_attributes,
            internal_attributes: $account->internal_attributes ?? [],
            features: $this->getSelectedFeatureFlags($account),
            manually_managed_features: $this->getManuallyManagedFeatures($account),
            selected_feature_flags: $this->getSelectedFeatureFlags($account),
            all_features: $this->getAllFeatures($account),
            account_users: $this->formatAccountUsers($account),
            status: $this->formatStatus($account->status),
            users_count: $account->users_count ?? 0,
            inboxes_count: $account->inboxes_count ?? 0,
            conversations_count: $account->conversations_count ?? 0,
            contacts_count: $account->contacts_count ?? 0,
            created_at: $account->created_at?->toIso8601String() ?? '',
            updated_at: $account->updated_at?->toIso8601String() ?? '',
        );
    }

    /**
     * Format an Account model to AccountListData DTO (for list view without account_users)
     */
    protected function formatAccountForList(Account $account): AccountListData
    {
        return new AccountListData(
            id: $account->id,
            name: $account->name,
            locale: $this->formatLocale($account->locale),
            domain: $account->domain,
            support_email: $account->support_email,
            auto_resolve_duration: $account->auto_resolve_duration,
            settings: $account->settings,
            limits: $this->formatLimits($account),
            custom_attributes: $account->custom_attributes,
            internal_attributes: $account->internal_attributes ?? [],
            features: $this->getSelectedFeatureFlags($account),
            manually_managed_features: $this->getManuallyManagedFeatures($account),
            selected_feature_flags: $this->getSelectedFeatureFlags($account),
            all_features: $this->getAllFeatures($account),
            status: $this->formatStatus($account->status),
            users_count: $account->users_count ?? 0,
            inboxes_count: $account->inboxes_count ?? 0,
            conversations_count: $account->conversations_count ?? 0,
            contacts_count: $account->contacts_count ?? 0,
            created_at: $account->created_at?->toIso8601String() ?? '',
            updated_at: $account->updated_at?->toIso8601String() ?? '',
        );
    }

    /**
     * Format locale to string code
     */
    private function formatLocale($locale): ?string
    {
        if ($locale instanceof \App\Enums\Locale) {
            return $locale->getCode();
        }
        
        if (is_int($locale)) {
            try {
                return \App\Enums\Locale::from($locale)->getCode();
            } catch (\ValueError) {
                return 'en'; // fallback
            }
        }
        
        return $locale;
    }

    /**
     * Format account status
     */
    protected function formatStatus(AccountStatus $status): string
    {
        return $status->getName();
    }

    /**
     * Parse status string to enum for database storage
     */
    protected function parseStatus(string $status): AccountStatus
    {
        return AccountStatus::fromString($status);
    }

    /**
     * Format account limits for super admin context
     */
    private function formatLimits(Account $account): array
    {
        $limits = $account->limits ?? [];
        
        // Ensure consistent structure matching Rails
        return [
            'agents' => $limits['agents'] ?? null,
            'inboxes' => $limits['inboxes'] ?? null,
            'captain_responses' => $limits['captain_responses'] ?? null,
            'captain_documents' => $limits['captain_documents'] ?? null,
        ];
    }

    /**
     * Get manually managed features (Chatwoot Cloud specific)
     */
    private function getManuallyManagedFeatures(Account $account): array
    {
        // Only show manually managed features in Chatwoot Cloud deployment
        if (!config('app.chatwoot_cloud', false)) {
            return [];
        }

        return $account->internal_attributes['manually_managed_features'] ?? [];
    }

    /**
     * Get selected feature flags (Rails-style enabled features)
     */
    private function getSelectedFeatureFlags(Account $account): array
    {
        $flagMap = [
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
        ];
        
        $selectedFlags = [];
        
        // Convert bitmask to array of enabled features
        foreach ($flagMap as $feature => $flag) {
            if (($account->feature_flags & $flag) !== 0) {
                $selectedFlags[] = $feature;
            }
        }
        
        // Add manually managed features if in Chatwoot Cloud
        if (config('app.chatwoot_cloud', false)) {
            $manuallyManaged = $this->getManuallyManagedFeatures($account);
            $selectedFlags = array_merge($selectedFlags, $manuallyManaged);
        }
        
        return array_unique($selectedFlags);
    }

    /**
     * Get all available features for super admin context
     */
    private function getAllFeatures(Account $account): array
    {
        // Base features available to all accounts
        $regularFeatures = [
            // Communication Channels
            'live_chat' => true,
            'email' => true,
            'sms' => true,
            'messenger' => true,
            'instagram' => true,
            'whatsapp' => true,
            'telegram' => true,
            'line' => true,
            'tiktok' => true,
            
            // Product Features
            'help_center' => true,
            'macros' => true,
            'canned_responses' => true,
            'labels' => true,
            'teams' => true,
            'custom_attributes' => true,
            'automation_rules' => true,
            'webhooks' => true,
            'campaigns' => true,
            'reports' => true,
            
            // OAuth & Authentication
            'google' => true,
            'microsoft' => true,
            
            // Third-party Integrations
            'linear' => true,
            'slack' => true,
            'shopify' => true,
        ];

        // Premium/Enterprise features
        $premiumFeatures = [
            'captain' => config('app.enterprise', false),
            'saml' => config('app.enterprise', false),
            'custom_branding' => config('app.enterprise', false),
            'agent_capacity' => config('app.enterprise', false),
            'audit_logs' => config('app.enterprise', false),
            'disable_branding' => config('app.enterprise', false),
            'advanced_reporting' => config('app.enterprise', false),
            'crm_integration' => config('app.enterprise', false),
            'notion_integration' => config('app.enterprise', false),
        ];

        // Merge features based on account capabilities
        $allFeatures = array_merge($regularFeatures, $premiumFeatures);

        // Apply account-specific feature overrides from bitmask
        $selectedFeatures = $this->getSelectedFeatureFlags($account);
        foreach ($selectedFeatures as $feature) {
            $allFeatures[$feature] = true;
        }

        // Apply manually managed features if in Chatwoot Cloud
        if (config('app.chatwoot_cloud', false)) {
            $manuallyManaged = $this->getManuallyManagedFeatures($account);
            foreach ($manuallyManaged as $feature) {
                $allFeatures[$feature] = true;
            }
        }

        return $allFeatures;
    }

    /**
     * Format account users for API response
     */
    private function formatAccountUsers(Account $account): array
    {
        if (!$account->relationLoaded('accountUsers')) {
            return [];
        }

        return $account->accountUsers->map(function ($accountUser) {
            return [
                'id' => $accountUser->id,
                'user_id' => $accountUser->user_id,
                'account_id' => $accountUser->account_id,
                'role' => $accountUser->role->value,
                'role_name' => $accountUser->role->getName(),
                'availability' => $accountUser->availability->value,
                'availability_name' => $accountUser->availability->getName(),
                'active_at' => $accountUser->active_at,
                'created_at' => $accountUser->created_at?->toIso8601String(),
                'updated_at' => $accountUser->updated_at?->toIso8601String(),
                'user' => $accountUser->user ? [
                    'id' => $accountUser->user->id,
                    'name' => $accountUser->user->name,
                    'email' => $accountUser->user->email,
                    'display_name' => $accountUser->user->display_name,
                ] : null,
                'inviter' => $accountUser->inviter ? [
                    'id' => $accountUser->inviter->id,
                    'name' => $accountUser->inviter->name,
                    'email' => $accountUser->inviter->email,
                    'display_name' => $accountUser->inviter->display_name,
                ] : null,
            ];
        })->toArray();
    }
}
