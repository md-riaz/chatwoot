<?php

namespace App\Actions\Account;

use App\Data\Account\AccountData;
use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateAccountAction
{
    use AsAction;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(Account $account, AccountData $data): Account
    {
        $this->accountRepository->update($account->id, $data->toArray());

        // Trigger event
        // event(new AccountUpdated($account));

        return $account->fresh();
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'locale' => ['sometimes', 'required', 'string', 'max:10'],
            'domain' => ['nullable', 'string'],
            'support_email' => ['nullable', 'email'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ];
    }
}
