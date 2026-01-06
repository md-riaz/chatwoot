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
            status: $account->status === 0 ? 'active' : 'suspended',
            users_count: $account->users_count ?? 0,
            inboxes_count: $account->inboxes_count ?? 0,
            conversations_count: $account->conversations_count ?? 0,
            created_at: $account->created_at->toIso8601String(),
            updated_at: $account->updated_at->toIso8601String(),
        );
    }
}
