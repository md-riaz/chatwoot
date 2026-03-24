<?php

namespace App\Services\Channels\Instagram;

use Illuminate\Http\Client\Factory as HttpFactory;

class InstagramGraphClient
{
    public function __construct(
        private HttpFactory $http
    ) {}

    public function authorizationUrl(string $state): string
    {
        return 'https://www.instagram.com/oauth/authorize?' . http_build_query([
            'client_id' => config('services.instagram.app_id'),
            'redirect_uri' => $this->redirectUri(),
            'scope' => 'instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments',
            'response_type' => 'code',
            'state' => $state,
            'enable_fb_login' => '0',
            'force_authentication' => '1',
        ]);
    }

    public function exchangeCodeForAccessToken(string $code): array
    {
        return $this->http->asForm()
            ->acceptJson()
            ->timeout(15)
            ->post($this->baseUrl() . '/oauth/access_token', [
                'client_id' => config('services.instagram.app_id'),
                'client_secret' => config('services.instagram.app_secret'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri(),
                'code' => $code,
            ])
            ->throw()
            ->json();
    }

    public function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        return $this->http->acceptJson()
            ->timeout(15)
            ->get($this->baseUrl() . '/' . $this->version() . '/access_token', [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => config('services.instagram.app_secret'),
                'access_token' => $shortLivedToken,
            ])
            ->throw()
            ->json();
    }

    public function getUserDetails(string $accessToken): array
    {
        return $this->http->acceptJson()
            ->timeout(15)
            ->get($this->baseUrl() . '/' . $this->version() . '/me', [
                'fields' => 'user_id,username',
                'access_token' => $accessToken,
            ])
            ->throw()
            ->json();
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.instagram.graph_url', 'https://graph.instagram.com'), '/');
    }

    private function version(): string
    {
        return trim(config('services.instagram.graph_version', 'v22.0'), '/');
    }

    private function redirectUri(): string
    {
        return config('services.instagram.redirect_uri') ?: route('instagram.oauth.callback');
    }
}
