<?php

namespace App\Http\Middleware;

use App\Models\Contact;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class WebSocketAuth
{
    /**
     * Handle an incoming request for WebSocket authentication.
     * Supports both agent authentication (via Sanctum) and contact authentication (via custom tokens).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Support agent authentication via Sanctum Bearer token
        if ($token = $request->bearerToken()) {
            $personalAccessToken = PersonalAccessToken::findToken($token);
            if ($personalAccessToken && $personalAccessToken->tokenable instanceof User) {
                Auth::setUser($personalAccessToken->tokenable);
                return $next($request);
            }
        }

        // Support contact authentication via custom contact token header
        if ($contactToken = $request->header('X-Contact-Token')) {
            $contact = Contact::where('pubsub_token', $contactToken)->first();
            if ($contact) {
                Auth::setUser($contact);
                return $next($request);
            }
        }

        // Support contact authentication via session (for widget)
        if ($contactId = $request->header('X-Contact-Id')) {
            $contact = Contact::find($contactId);
            if ($contact) {
                // Additional validation could be added here (e.g., session verification)
                Auth::setUser($contact);
                return $next($request);
            }
        }

        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'Invalid authentication credentials for WebSocket connection'
        ], 401);
    }
}