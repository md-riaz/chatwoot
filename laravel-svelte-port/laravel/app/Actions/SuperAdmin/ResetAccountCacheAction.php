<?php

namespace App\Actions\SuperAdmin;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class ResetAccountCacheAction
{
    use AsAction;

    public function handle(Account $account): void
    {
        Cache::forget("account_{$account->id}_settings");
        Cache::forget("account_{$account->id}_features");
        Cache::tags(["account_{$account->id}"])->flush();
    }
}
