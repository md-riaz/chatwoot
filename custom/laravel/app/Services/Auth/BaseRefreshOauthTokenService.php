<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

/**
 * Centralized OAuth token refresh helper.
 * Mirrors Rails `BaseRefreshOauthTokenService` behavior: performs a provider token exchange
 * using a refresh token and returns normalized fields.
 *
 * @see app/services/base_refresh_oauth_token_service.rb
 */
class BaseRefreshOauthTokenService
{
    /**
     * Refresh an access token using the provided parameters.
     *
     * Required params:
     * - token_url
     * - client_id
     * - client_secret
     * - refresh_token
     *
     * Returns an array with keys: access_token, refresh_token, expires_at (Carbon|null), raw
     *
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function refreshTokenFor(array $params): array
    {
        $tokenUrl = $params['token_url'] ?? null;
        $clientId = $params['client_id'] ?? null;
        $clientSecret = $params['client_secret'] ?? null;
        $refreshToken = $params['refresh_token'] ?? null;
        $scope = $params['scope'] ?? null;

        if (! $tokenUrl || ! $clientId || ! $clientSecret || ! $refreshToken) {
            throw new \InvalidArgumentException('Missing required parameters for token refresh');
        }

        $payload = array_filter([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
        ]);

        $response = Http::asForm()->post($tokenUrl, $payload);

        if (! $response->successful()) {
            $msg = 'OAuth token refresh failed';
            $body = $response->body();
            throw new \RuntimeException($msg . ' - ' . $body);
        }

        $data = $response->json();

        $accessToken = $data['access_token'] ?? null;
        if (! $accessToken) {
            throw new \RuntimeException('OAuth provider did not return access_token');
        }

        $expiresAt = null;
        if (isset($data['expires_in'])) {
            $expiresAt = Carbon::now()->addSeconds((int) $data['expires_in']);
        } elseif (isset($data['expires_at'])) {
            $expiresAt = Carbon::parse($data['expires_at']);
        }

        return [
            'access_token' => $accessToken,
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => $expiresAt,
            'raw' => $data,
        ];
    }
}
