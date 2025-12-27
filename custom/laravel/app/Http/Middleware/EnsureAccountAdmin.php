<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user is an administrator (role = 2) of the account.
     * This middleware should be used after EnsureAccountAccess.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->route('account');

        if (! $account) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Check if user is an admin of this account
        $accountUser = $account->users()->where('user_id', $user->id)->first();

        if (! $accountUser || $accountUser->pivot->role < 2) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        return $next($request);
    }
}
