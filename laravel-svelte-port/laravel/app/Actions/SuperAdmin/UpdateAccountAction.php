<?php

namespace App\Actions\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Data\SuperAdmin\AccountData;
use App\Repositories\SuperAdmin\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateAccountAction
{
    use AsAction;
    use FormatsAccountData;

    public function handle(int $id, AccountData $data): AccountData
    {
        $accountRepository = app(AccountRepository::class);
        
        $account = $accountRepository->find($id);

        if (! $account) {
            throw new \Exception("Account not found");
        }

        // Convert locale string to enum if needed
        $localeValue = $data->locale;
        if (is_string($data->locale)) {
            try {
                $localeEnum = \App\Enums\Locale::fromCode($data->locale);
                $localeValue = $localeEnum->value;
            } catch (\InvalidArgumentException $e) {
                // Keep existing locale if invalid code provided
                $localeValue = $account->locale instanceof \App\Enums\Locale ? $account->locale->value : $account->locale;
            }
        }

        // Handle manually_managed_features in internal_attributes
        $internalAttributes = $data->internal_attributes ?? $account->internal_attributes ?? [];
        if ($data->manually_managed_features !== null) {
            $internalAttributes['manually_managed_features'] = $data->manually_managed_features;
        }
        
        // Handle selected_feature_flags - convert to feature_flags bitmask
        $featureFlags = $account->feature_flags ?? 0;
        if ($data->selected_feature_flags !== null) {
            // Convert feature flags array to bitmask (Rails-style)
            $featureFlags = 0;
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
            
            foreach ($data->selected_feature_flags as $flag) {
                if (isset($flagMap[$flag])) {
                    $featureFlags |= $flagMap[$flag];
                }
            }
        }

        $accountRepository->update($id, [
            'name' => $data->name,
            'locale' => $localeValue,
            'domain' => $data->domain,
            'support_email' => $data->support_email,
            'auto_resolve_duration' => $data->auto_resolve_duration,
            'settings' => $data->settings,
            'limits' => $data->limits,
            'custom_attributes' => $data->custom_attributes,
            'internal_attributes' => $internalAttributes,
            'feature_flags' => $featureFlags,
            'status' => $data->status === 'active' ? 0 : 1,
        ]);

        $account->refresh();

        return $this->formatAccount($account);
    }
}
