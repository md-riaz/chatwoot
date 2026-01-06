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
            'features' => $data->features,
            'status' => $data->status === 'active' ? 0 : 1,
        ]);

        $account->refresh();

        return $this->formatAccount($account);
    }
}
