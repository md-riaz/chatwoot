<?php

namespace App\Http\Middleware;

use App\Models\AccountUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomRolePermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get account ID from route parameters
        $accountId = $request->route('account')?->id ?? $request->route('accountId');
        if (!$accountId) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        // Get account user relationship
        $accountUser = AccountUser::where('user_id', $user->id)
            ->where('account_id', $accountId)
            ->with('customRole')
            ->first();

        if (!$accountUser) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if user has the required permission
        if (!$accountUser->hasPermission($permission)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        return $next($request);
    }
}