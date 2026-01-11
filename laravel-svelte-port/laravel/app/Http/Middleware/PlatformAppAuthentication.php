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
 * This middleware authenticates the request using the sanctum guard (with multi-model support)
 * and validates that the authenticated entity is a PlatformApp.
 * Sets the platform_app in request attributes for downstream use.
 * 
 * This middleware handles both authentication AND validation in one step.
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
        // Authenticate using sanctum guard (which supports User, AgentBot, PlatformApp)
        $authenticatedEntity = Auth::guard('sanctum')->user();
        
        // Return 401 if not authenticated or not a PlatformApp
        if (!$authenticatedEntity instanceof PlatformApp) {
            return response()->json([
                'error' => 'Invalid access_token'
            ], 401);
        }
        
        // Set platform_app in request attributes for downstream use
        $request->attributes->set('platform_app', $authenticatedEntity);
        
        // Also set as the authenticated user for the request
        Auth::setUser($authenticatedEntity);
        
        return $next($request);
    }
}
