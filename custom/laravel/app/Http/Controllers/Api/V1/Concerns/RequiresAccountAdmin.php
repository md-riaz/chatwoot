<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Account;
use Illuminate\Http\Request;

trait RequiresAccountAdmin
{
    /**
     * Ensure the current user is an admin of the account.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function ensureAdmin(Request $request, Account $account): void
    {
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();

        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Admin access required');
        }
    }

    /**
     * Check if the current user is an admin of the account.
     */
    protected function isAdmin(Request $request, Account $account): bool
    {
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();

        return $accountUser && $accountUser->pivot->role >= 2;
    }
}
