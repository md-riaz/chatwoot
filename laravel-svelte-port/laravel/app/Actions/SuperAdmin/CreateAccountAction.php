<?php

namespace App\Actions\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Data\SuperAdmin\AccountData;
use App\Enums\AccountStatus;
use App\Models\Account;
use App\Repositories\SuperAdmin\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAccountAction
{
    use AsAction;
    use FormatsAccountData;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(AccountData $data): AccountData
    {
        $account = $this->accountRepository->create([
            'name' => $data->name,
            'locale' => $this->resolveLocale($data->locale),
            'domain' => $data->domain,
            'support_email' => $data->support_email,
            'auto_resolve_duration' => $data->auto_resolve_duration,
            'settings' => $data->settings,
            'limits' => $data->limits,
            'custom_attributes' => $data->custom_attributes,
            'internal_attributes' => $this->resolveInternalAttributes($data),
            'feature_flags' => 0,
            'status' => AccountStatus::fromString($data->status),
        ]);

        $this->syncSelectedFeatures($account, $data->selected_feature_flags ?? $data->enabled_features ?? []);
        $account->loadCount(['users', 'inboxes', 'conversations', 'contacts']);

        return $this->formatAccount($account);
    }

    private function resolveLocale(?string $locale): int
    {
        if ($locale === null) {
            return \App\Enums\Locale::EN->value;
        }

        try {
            return \App\Enums\Locale::fromCode($locale)->value;
        } catch (\InvalidArgumentException) {
            return \App\Enums\Locale::EN->value;
        }
    }

    private function resolveInternalAttributes(AccountData $data): array
    {
        $internalAttributes = $data->internal_attributes ?? [];

        if ($data->manually_managed_features !== null) {
            $internalAttributes['manually_managed_features'] = $data->manually_managed_features;
        }

        return $internalAttributes;
    }

    /**
     * @param array<int, string> $selectedFeatures
     */
    private function syncSelectedFeatures(Account $account, array $selectedFeatures): void
    {
        $selectedFeatures = array_values(array_unique($selectedFeatures));
        $enterpriseFeatures = ['saml', 'sla', 'custom_roles', 'audit_logs', 'advanced_search', 'companies'];
        $featureFlags = 0;
        $flagMap = $account->getFeatureFlagMap();

        foreach ($selectedFeatures as $feature) {
            if (isset($flagMap[$feature])) {
                $featureFlags |= $flagMap[$feature];
            }
        }

        $customAttributes = $account->custom_attributes ?? [];
        $customAttributes['enabled_enterprise_features'] = array_values(array_filter(
            $selectedFeatures,
            static fn (string $feature) => in_array($feature, $enterpriseFeatures, true)
        ));

        $account->forceFill([
            'feature_flags' => $featureFlags,
            'custom_attributes' => $customAttributes,
        ])->save();
    }
}
