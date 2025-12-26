<?php

namespace App\Actions\Account;

use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAccountAction
{
    use AsAction;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(Account $account): bool
    {
        // Trigger event
        // event(new AccountDeleted($account));

        return $this->accountRepository->delete($account->id);
    }
}
