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
 * Matches Rails behavior in app/controllers/concerns/access_token_auth_helper.rb
 */
class ValidateBotAccess
{
    /**
     * Bot accessible endpoints mapping (matches Rails BOT_ACCESSIBLE_ENDPOINTS)
     * 
     * Format: 'controller_path' => ['allowed_actions']
     * 
     * Rails reference:
     * BOT_ACCESSIBLE_ENDPOINTS = {
     *   'api/v1/accounts/conversations' => %w[toggle_status toggle_priority create update custom_attributes],
     *   'api/v1/accounts/conversations/messages' => ['create'],
     *   'api/v1/accounts/conversations/assignments' => ['create']
     * }.freeze
     */
    public const BOT_ACCESSIBLE_ENDPOINTS = [
        'api/v1/accounts/conversations' => [
            'toggleStatus',      // Rails: toggle_status
            'togglePriority',    // Rails: toggle_priority
            'store',             // Rails: create
            'update',            // Rails: update
            'customAttributes',  // Rails: custom_attributes
        ],
        'api/v1/accounts/conversations/messages' => [
            'store',             // Rails: create
        ],
        'api/v1/accounts/conversations/assignments' => [
            'store',             // Rails: create (handled by assign action in ConversationsController)
        ],
    ];

    /**
     * Mapping of Laravel controller classes to Rails-style controller paths.
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
        
        // Skip validation for non-bot users (matches Rails: return if Current.user.is_a?(User))
        // If no user is authenticated or user is not an AgentBot, allow the request
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
        
        // Get allowed actions for this controller path
        $allowedActions = self::BOT_ACCESSIBLE_ENDPOINTS[$controllerPath] ?? [];
        
        return in_array($action, $allowedActions, true);
    }

    /**
     * Get the Rails-style controller path from a Laravel route.
     * 
     * @param \Illuminate\Routing\Route $route
     */
    protected function getControllerPath($route): string
    {
        $controller = $route->getControllerClass();
        
        // First check explicit mapping
        if (isset(self::CONTROLLER_PATH_MAPPING[$controller])) {
            return self::CONTROLLER_PATH_MAPPING[$controller];
        }
        
        // Fallback: try to derive path from controller class name
        // App\Http\Controllers\Api\V1\ConversationsController -> api/v1/accounts/conversations
        return '';
    }
}
