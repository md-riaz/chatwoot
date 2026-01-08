<?php

namespace App\Http\Middleware;

use App\Helpers\SamlAuthenticationHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SamlAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a password authentication attempt for a SAML user
        if ($request->isMethod('POST') && $request->has('email')) {
            $email = $request->input('email');
            $ssoAuthToken = $request->input('sso_auth_token');
            
            if (SamlAuthenticationHelper::samlUserAttemptingPasswordAuth($email, $ssoAuthToken)) {
                return response()->json([
                    'error' => 'SAML users must authenticate through SSO',
                    'saml_required' => true,
                    'message' => 'This account uses SAML SSO. Please use the SSO login button.',
                ], 403);
            }
        }

        return $next($request);
    }
}