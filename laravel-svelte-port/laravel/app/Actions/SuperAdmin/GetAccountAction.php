<?php

namespace App\Actions\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Data\SuperAdmin\AccountData;
use App\Repositories\SuperAdmin\AccountRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAccountAction
{
    use AsAction;
    use FormatsAccountData;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(int $id): AccountData
    {
        $account = $this->accountRepository->getWithDetails($id);

        if (! $account) {
            throw new ModelNotFoundException('Account not found');
        }

        return $this->formatAccount($account);
    }
}
