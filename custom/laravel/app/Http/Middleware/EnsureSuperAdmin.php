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
        
        // Check both the type field (Rails parity) and Spatie role (Laravel implementation)
        if (! $user || 
            ($user->type !== 'SuperAdmin' && ! $user->hasRole('super_admin'))) {
            return response()->json([
                'error' => 'Unauthorized. Super admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
