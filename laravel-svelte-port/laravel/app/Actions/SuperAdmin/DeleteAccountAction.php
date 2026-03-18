<?php

namespace App\Actions\SuperAdmin;

use App\Models\Account;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAccountAction
{
    use AsAction;

    public function handle(Account $account): void
    {
        $account->delete();
    }
}
