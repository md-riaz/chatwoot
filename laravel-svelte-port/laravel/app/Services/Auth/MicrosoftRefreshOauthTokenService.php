<?php

namespace App\Services\Auth;

use App\Models\Channels\Email;
use App\Services\GlobalConfigService;
use Carbon\Carbon;

/**
 * Microsoft OAuth token refresh service for email channels.
 * Handles token refresh for Outlook/Exchange OAuth integration.
 * 
 * @see app/services/microsoft/refresh_oauth_token_service.rb
 */
class MicrosoftRefreshOauthTokenService
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
            throw new \RuntimeException('No refresh token available for Microsoft OAuth');
        }

        $clientId = GlobalConfigService::load('AZURE_APP_ID');
        $clientSecret = GlobalConfigService::load('AZURE_APP_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            throw new \RuntimeException('Microsoft OAuth credentials not configured');
        }

        $refreshedTokens = $this->baseService->refreshTokenFor([
            'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $providerConfig['refresh_token'],
            'scope' => 'https://graph.microsoft.com/IMAP.AccessAsUser.All https://graph.microsoft.com/SMTP.Send offline_access',
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