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

        $accountRepository->update($id, [
            'name' => $data->name,
            'locale' => $data->locale,
            'domain' => $data->domain,
            'support_email' => $data->support_email,
            'auto_resolve_duration' => $data->auto_resolve_duration,
            'settings' => $data->settings,
            'limits' => $data->limits,
            'status' => $data->status === 'active' ? 0 : 1,
        ]);

        $account->refresh();

        return $this->formatAccount($account);
    }
}
