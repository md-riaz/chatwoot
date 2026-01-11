<?php

namespace App\Http\Middleware;

use App\Models\PlatformApp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for validating platform app resource permissions.
 * 
 * This middleware checks if the platform app has permission to access
 * the requested resource by checking the platform_app_permissibles table.
 * 
 * Matches Rails behavior in app/controllers/platform_controller.rb#validate_platform_app_permissible
 */
class ValidatePlatformPermissible
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var PlatformApp|null $platformApp */
        $platformApp = $request->attributes->get('platform_app');
        
        // Skip validation if no platform app (should be caught by PlatformAppAuthentication)
        if (!$platformApp) {
            return $next($request);
        }
        
        // Get the resource from route model binding
        // Check for common resource parameter names used in platform routes
        $resource = $this->getResourceFromRoute($request);
        
        // Skip validation if no resource to validate
        // This matches Rails behavior where validation only runs on show, update, destroy actions
        if (!$resource) {
            return $next($request);
        }
        
        // Set the resource in request attributes for downstream use
        $request->attributes->set('resource', $resource);
        
        // Check if the platform app has permission to access this resource
        $hasPermission = $platformApp->permissibles()
            ->where('permissible_type', get_class($resource))
            ->where('permissible_id', $resource->id)
            ->exists();
        
        if (!$hasPermission) {
            return response()->json([
                'error' => 'Non permissible resource'
            ], 401);
        }
        
        return $next($request);
    }
    
    /**
     * Get the resource from route model binding.
     * 
     * Checks for common parameter names used in platform routes:
     * - user, account, agentBot (route model binding)
     */
    protected function getResourceFromRoute(Request $request): ?object
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }
        
        // Check for common resource parameter names
        $resourceParams = ['user', 'account', 'agentBot', 'agent_bot'];
        
        foreach ($resourceParams as $param) {
            $resource = $route->parameter($param);
            if ($resource && is_object($resource)) {
                return $resource;
            }
        }
        
        return null;
    }
}
