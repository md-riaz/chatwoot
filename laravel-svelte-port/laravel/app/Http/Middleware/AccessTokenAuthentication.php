<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use App\Models\AgentBot;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for authenticating API requests using access tokens.
 * 
 * This middleware checks for the `api_access_token` or `HTTP_API_ACCESS_TOKEN` header
 * and authenticates the request based on the token's owner (User, AgentBot, or PlatformApp).
 * 
 * Matches Rails behavior in app/controllers/concerns/access_token_auth_helper.rb
 */
class AccessTokenAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for access token in headers (Rails parity: api_access_token or HTTP_API_ACCESS_TOKEN)
        $token = $request->header('api_access_token') 
            ?? $request->header('HTTP_API_ACCESS_TOKEN');
        
        // If no access token header, let other auth middleware handle it (fallback to Sanctum/session)
        if (empty($token)) {
            return $next($request);
        }
        
        // Look up the access token by token value
        $accessToken = AccessToken::where('token', $token)->first();
        
        // Return 401 for invalid tokens
        if (!$accessToken) {
            return response()->json([
                'error' => 'Invalid Access Token'
            ], 401);
        }
        
        // Get the token's owner (User, AgentBot, or PlatformApp)
        $owner = $accessToken->owner;
        
        // Set request attributes for downstream use
        $request->attributes->set('access_token', $accessToken);
        $request->attributes->set('access_token_resource', $owner);
        
        // Set authenticated user for User or AgentBot owners
        // This matches Rails: Current.user = @resource if allowed_current_user_type?(@resource)
        // In Rails, allowed_current_user_type? returns true for User and AgentBot
        if ($owner instanceof User || $owner instanceof AgentBot) {
            Auth::setUser($owner);
        }
        
        return $next($request);
    }
}
