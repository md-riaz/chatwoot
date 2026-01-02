<?php

namespace App\Services\Auth;

use App\Models\Channels\Email;
use App\Services\GlobalConfigService;
use Carbon\Carbon;

/**
 * Google OAuth token refresh service for email channels.
 * Handles token refresh for Gmail OAuth integration.
 * 
 * @see app/services/google/refresh_oauth_token_service.rb
 */
class GoogleRefreshOauthTokenService
{
    protected Email $channel;
    protected BaseRefreshOauthTokenService $baseService;

    public function __construct(Email $channel)
    {
        $this->channel = $channel;
        $this->baseService = new BaseRefreshOauthTokenService();
    }

    /**
     * Get a valid access token, refreshing if necessary.
     */
    public function getAccessToken(): ?string
    {
        $providerConfig = $this->channel->provider_config ?? [];
        
        if (empty($providerConfig['access_token'])) {
            return null;
        }

        if (!$this->isAccessTokenExpired()) {
            return $providerConfig['access_token'];
        }

        $refreshedTokens = $this->refreshTokens();
        return $refreshedTokens['access_token'] ?? null;
    }

    /**
     * Check if the access token is expired.
     */
    public function isAccessTokenExpired(): bool
    {
        $providerConfig = $this->channel->provider_config ?? [];
        $expiresOn = $providerConfig['expires_on'] ?? null;

        if (empty($expiresOn)) {
            return true;
        }

        // Adding a 5 minute window to expiry check to avoid race conditions
        return Carbon::now()->utc()->gte(Carbon::parse($expiresOn)->subMinutes(5));
    }

    /**
     * Refresh the access tokens using the refresh token.
     */
    public function refreshTokens(): array
    {
        $providerConfig = $this->channel->provider_config ?? [];
        
        if (empty($providerConfig['refresh_token'])) {
            throw new \RuntimeException('No refresh token available for Google OAuth');
        }

        $clientId = GlobalConfigService::load('GOOGLE_OAUTH_CLIENT_ID');
        $clientSecret = GlobalConfigService::load('GOOGLE_OAUTH_CLIENT_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            throw new \RuntimeException('Google OAuth credentials not configured');
        }

        $refreshedTokens = $this->baseService->refreshTokenFor([
            'token_url' => 'https://oauth2.googleapis.com/token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $providerConfig['refresh_token'],
            'scope' => 'https://www.googleapis.com/auth/gmail.readonly https://www.googleapis.com/auth/gmail.send',
        ]);

        $this->updateChannelProviderConfig($refreshedTokens);
        
        return $refreshedTokens;
    }

    /**
     * Update the channel's provider config with new tokens.
     */
    protected function updateChannelProviderConfig(array $newTokens): void
    {
        $this->channel->provider_config = [
            'access_token' => $newTokens['access_token'],
            'refresh_token' => $newTokens['refresh_token'],
            'expires_on' => $newTokens['expires_at']?->utc()->toISOString(),
        ];
        
        $this->channel->save();
    }
}