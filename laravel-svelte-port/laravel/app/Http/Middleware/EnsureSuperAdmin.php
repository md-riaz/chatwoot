<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Check the type field (Rails parity) - super_admin is now a user type, not a role
        if (! $user || $user->type !== 'SuperAdmin') {
            return response()->json([
                'error' => 'Unauthorized. Super admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
