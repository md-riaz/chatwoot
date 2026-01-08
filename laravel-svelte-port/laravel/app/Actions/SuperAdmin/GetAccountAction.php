<?php

namespace App\Actions\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Data\SuperAdmin\AccountData;
use App\Models\Account;
use App\Repositories\SuperAdmin\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAccountAction
{
    use AsAction;
    use FormatsAccountData;

    public function handle(int $id): AccountData
    {
        $accountRepository = app(AccountRepository::class);
        $account = $accountRepository->getWithDetails($id);

        if (! $account) {
            throw new \Exception("Account not found");
        }

        return $this->formatAccount($account);
    }
}
