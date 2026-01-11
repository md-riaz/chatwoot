<?php

namespace App\Http\Middleware;

use App\Models\AgentBot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for validating bot access to API endpoints.
 * 
 * This middleware restricts AgentBot users to only access specific endpoints
 * defined in BOT_ACCESSIBLE_ENDPOINTS. User-authenticated requests bypass
 * this validation entirely.
 * 
 * Should be used AFTER auth:sanctum middleware.
 */
class ValidateBotAccess
{
    /**
     * Bot accessible endpoints mapping.
     * 
     * Format: 'controller_path' => ['allowed_actions']
     */
    public const BOT_ACCESSIBLE_ENDPOINTS = [
        'api/v1/accounts/conversations' => [
            'toggleStatus',
            'togglePriority',
            'store',
            'update',
            'customAttributes',
        ],
        'api/v1/accounts/conversations/messages' => [
            'store',
        ],
        'api/v1/accounts/conversations/assignments' => [
            'store',
        ],
    ];

    /**
     * Mapping of Laravel controller classes to controller paths.
     */
    protected const CONTROLLER_PATH_MAPPING = [
        'App\Http\Controllers\Api\V1\ConversationsController' => 'api/v1/accounts/conversations',
        'App\Http\Controllers\Api\V1\MessagesController' => 'api/v1/accounts/conversations/messages',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Skip validation for non-bot users
        if (!$user instanceof AgentBot) {
            return $next($request);
        }
        
        // Check if endpoint is accessible to bots
        if (!$this->isBotAccessible($request)) {
            return response()->json([
                'error' => 'Access to this endpoint is not authorized for bots'
            ], 401);
        }
        
        return $next($request);
    }

    /**
     * Check if the current endpoint is accessible to bots.
     */
    protected function isBotAccessible(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }
        
        $action = $route->getActionMethod();
        $controllerPath = $this->getControllerPath($route);
        
        $allowedActions = self::BOT_ACCESSIBLE_ENDPOINTS[$controllerPath] ?? [];
        
        return in_array($action, $allowedActions, true);
    }

    /**
     * Get the controller path from a Laravel route.
     * 
     * @param \Illuminate\Routing\Route $route
     */
    protected function getControllerPath($route): string
    {
        $controller = $route->getControllerClass();
        
        return self::CONTROLLER_PATH_MAPPING[$controller] ?? '';
    }
}
