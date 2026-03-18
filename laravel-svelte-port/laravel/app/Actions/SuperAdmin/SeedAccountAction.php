<?php

namespace App\Actions\SuperAdmin;

use App\Jobs\SeedAccountJob;
use App\Models\Account;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedAccountAction
{
    use AsAction;

    public function handle(Account $account): void
    {
        SeedAccountJob::dispatch($account);
    }
}
