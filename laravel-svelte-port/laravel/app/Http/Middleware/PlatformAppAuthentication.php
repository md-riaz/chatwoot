<?php

namespace App\Http\Middleware;

use App\Models\PlatformApp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for authenticating platform API requests.
 * 
 * This middleware validates that the authenticated user (via Sanctum) is a PlatformApp
 * and sets the platform_app in request attributes for downstream use.
 * 
 * Should be used AFTER auth:sanctum middleware.
 */
class PlatformAppAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated user from Sanctum
        $user = Auth::user();
        
        // Return 401 if not authenticated or not a PlatformApp
        if (!$user instanceof PlatformApp) {
            return response()->json([
                'error' => 'Invalid access_token'
            ], 401);
        }
        
        // Set platform_app in request attributes for downstream use
        $request->attributes->set('platform_app', $user);
        
        return $next($request);
    }
}
