<?php

namespace App\Helpers;

use App\Models\User;

class SamlAuthenticationHelper
{
    /**
     * Check if a SAML user is attempting password authentication
     * 
     * @param string|null $email
     * @param string|null $ssoAuthToken
     * @return bool
     */
    public static function samlUserAttemptingPasswordAuth(?string $email, ?string $ssoAuthToken = null): bool
    {
        if (empty($email)) {
            return false;
        }

        $user = User::where('email', $email)->first();
        if (!$user || $user->provider !== 'saml') {
            return false;
        }

        // If SSO auth token is present and valid, allow the authentication
        if (!empty($ssoAuthToken) && $user->validSsoAuthToken($ssoAuthToken)) {
            return false;
        }

        return true;
    }

    /**
     * Prevent password authentication for SAML users
     * 
     * @param string $email
     * @return bool
     */
    public static function shouldPreventPasswordAuth(string $email): bool
    {
        return self::samlUserAttemptingPasswordAuth($email);
    }
}