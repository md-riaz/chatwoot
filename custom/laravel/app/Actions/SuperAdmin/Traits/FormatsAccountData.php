<?php

namespace App\Actions\SuperAdmin\Traits;

use App\Data\SuperAdmin\AccountData;
use App\Models\Account;

trait FormatsAccountData
{
    /**
     * Format an Account model to AccountData DTO
     */
    protected function formatAccount(Account $account): AccountData
    {
        return new AccountData(
            id: $account->id,
            name: $account->name,
            locale: $account->locale instanceof \App\Enums\Locale ? $account->locale->getCode() : $account->locale,
            domain: $account->domain,
            support_email: $account->support_email,
            auto_resolve_duration: $account->auto_resolve_duration,
            settings: $account->settings,
            limits: $account->limits,
            custom_attributes: $account->custom_attributes,
            internal_attributes: $account->internal_attributes,
            features: $account->features,
            manually_managed_features: $account->internal_attributes['manually_managed_features'] ?? [],
            all_features: $this->getAllFeatures($account),
            status: $account->status === 0 ? 'active' : 'suspended',
            users_count: $account->users_count ?? 0,
            inboxes_count: $account->inboxes_count ?? 0,
            conversations_count: $account->conversations_count ?? 0,
            contacts_count: $account->contacts_count ?? 0,
            created_at: $account->created_at->toIso8601String(),
            updated_at: $account->updated_at->toIso8601String(),
        );
    }

    /**
     * Get all available features for super admin context
     */
    private function getAllFeatures(Account $account): array
    {
        // This would be the equivalent of Rails AccountFeaturesField
        // Return available features based on enterprise/community edition
        $features = [
            // Communication Channels
            'live_chat' => true,
            'email' => true,
            'sms' => true,
            'messenger' => true,
            'instagram' => true,
            'whatsapp' => true,
            'telegram' => true,
            'line' => true,
            
            // Product Features
            'help_center' => true,
            
            // OAuth & Authentication
            'google' => true,
            'microsoft' => true,
            
            // Third-party Integrations
            'linear' => true,
            'slack' => true,
            'shopify' => true,
        ];

        // Add enterprise features if available
        if (config('app.enterprise', false)) {
            $features = array_merge($features, [
                'captain' => true,
                'saml' => true,
                'custom_branding' => true,
                'agent_capacity' => true,
                'audit_logs' => true,
                'disable_branding' => true,
            ]);
        }

        return $features;
    }
}
