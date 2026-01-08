<?php

namespace App\Services\Channels\Tiktok;

use App\Models\Channels\TikTok;
use App\Services\GlobalConfigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * TikTok token management service.
 * Handles access token refresh logic for TikTok channels.
 * 
 * @see app/services/tiktok/token_service.rb
 */
class TiktokTokenService
{
    protected TikTok $channel;

    public function __construct(TikTok $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Returns a valid access token, refreshing it if necessary and eligible.
     */
    public function getAccessToken(): ?string
    {
        if ($this->isTokenValid()) {
            return $this->channel->access_token;
        }

        if ($this->isRefreshTokenValid()) {
            return $this->refreshAccessToken();
        }

        // Token and refresh token are both expired - need reauthorization
        Log::warning('TikTok channel requires reauthorization', [
            'channel_id' => $this->channel->id,
            'business_id' => $this->channel->business_id
        ]);

        return $this->channel->access_token;
    }

    /**
     * Check if the current token is still valid (not expired).
     */
    protected function isTokenValid(): bool
    {
        if (!$this->channel->expires_at) {
            return false;
        }

        // Adding 5 minutes buffer to avoid race conditions
        return Carbon::now()->addMinutes(5)->lt($this->channel->expires_at);
    }

    /**
     * Check if the refresh token is still valid.
     */
    protected function isRefreshTokenValid(): bool
    {
        if (!$this->channel->refresh_token_expires_at) {
            return false;
        }

        return Carbon::now()->lt($this->channel->refresh_token_expires_at);
    }

    /**
     * Makes an API request to refresh the access token.
     */
    protected function refreshAccessToken(): ?string
    {
        $lockKey = "tiktok_refresh_token_mutex:{$this->channel->id}";
        
        // Use cache lock to prevent concurrent refresh attempts
        $lock = Cache::lock($lockKey, 30);

        try {
            if (!$lock->get()) {
                // Could not acquire lock, another process is likely refreshing
                // Return current token as it should still be valid
                return $this->channel->access_token;
            }

            $result = $this->attemptRefreshToken();
            
            $this->channel->update([
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'expires_at' => $result['expires_at'],
                'refresh_token_expires_at' => $result['refresh_token_expires_at'],
            ]);

            Log::info('TikTok access token refreshed successfully', [
                'channel_id' => $this->channel->id,
                'business_id' => $this->channel->business_id
            ]);

            return $result['access_token'];

        } catch (\Exception $e) {
            Log::error('Failed to refresh TikTok access token', [
                'channel_id' => $this->channel->id,
                'business_id' => $this->channel->business_id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        } finally {
            $lock->release();
        }
    }

    /**
     * Attempt to refresh the token using TikTok API.
     */
    protected function attemptRefreshToken(): array
    {
        $clientId = GlobalConfigService::load('TIKTOK_APP_ID');
        $clientSecret = GlobalConfigService::load('TIKTOK_APP_SECRET');

        if (empty($clientId) || empty($clientSecret)) {
            throw new \RuntimeException('TikTok app credentials not configured');
        }

        $response = Http::asJson()->post('https://business-api.tiktok.com/open_api/v1.3/tt_user/oauth2/refresh_token/', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->channel->refresh_token,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("TikTok token refresh failed: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();

        if (($data['code'] ?? null) !== 0) {
            throw new \RuntimeException("TikTok API error: {$data['code']} - {$data['message']}");
        }

        $tokenData = $data['data'];

        return [
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_at' => Carbon::now()->addSeconds($tokenData['expires_in']),
            'refresh_token_expires_at' => Carbon::now()->addSeconds($tokenData['refresh_token_expires_in']),
        ];
    }
}