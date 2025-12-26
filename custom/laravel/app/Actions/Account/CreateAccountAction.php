<?php

namespace App\Actions\Account;

use App\Data\Account\AccountData;
use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAccountAction
{
    use AsAction;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(AccountData $data): Account
    {
        $account = $this->accountRepository->create($data->toArray());

        // Trigger event
        // event(new AccountCreated($account));

        return $account;
    }

    public function rules(): array
    {
        return AccountData::rules();
    }
}
