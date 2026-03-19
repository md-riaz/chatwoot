<?php

namespace App\Actions\SuperAdmin\Traits;

use App\Data\SuperAdmin\AccountData;
use App\Data\SuperAdmin\AccountListData;
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

    private function formatStatus($status): string
    {
        return method_exists($status, 'getName') ? $status->getName() : (string) $status;
    }

    /**
     * Format account limits for super admin context
     */
    private function formatLimits(Account $account): array
    {
        return $account->limits ?? [];
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
        return $account->getEnabledFeatures();
    }

    /**
     * Get all available features for super admin context
     */
    private function getAllFeatures(Account $account): array
    {
        $allFeatures = [];

        foreach (config('features.features', []) as $feature) {
            $name = $feature['name'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            $allFeatures[$name] = [
                'available' => true,
                'display_name' => $feature['display_name'] ?? ucwords(str_replace('_', ' ', $name)),
                'enabled' => $feature['enabled'] ?? false,
                'premium' => $feature['premium'] ?? false,
                'help_url' => $feature['help_url'] ?? null,
            ];
        }

        foreach ($this->getSelectedFeatureFlags($account) as $feature) {
            $allFeatures[$feature] ??= [
                'available' => true,
                'display_name' => ucwords(str_replace('_', ' ', $feature)),
                'enabled' => false,
                'premium' => false,
                'help_url' => null,
            ];
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
