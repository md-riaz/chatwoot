<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has access to the account specified in the route.
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

        // Check if user has access to this account
        $hasAccess = $account->users()->where('user_id', $user->id)->exists();

        if (! $hasAccess) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        return $next($request);
    }
}
