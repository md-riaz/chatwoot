<?php

namespace Tests\Unit\Services;

use App\Services\Auth\BaseRefreshOauthTokenService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BaseRefreshOauthTokenServiceTest extends TestCase
{
    public function test_refresh_token_success()
    {
        Http::fake([
            'https://provider.example/oauth/token' => Http::response([
                'access_token' => 'new-token',
                'refresh_token' => 'new-refresh',
                'expires_in' => 3600,
            ], 200),
        ]);

        $svc = new BaseRefreshOauthTokenService();

        $result = $svc->refreshTokenFor([
            'token_url' => 'https://provider.example/oauth/token',
            'client_id' => 'cid',
            'client_secret' => 'csecret',
            'refresh_token' => 'old-refresh',
        ]);

        $this->assertEquals('new-token', $result['access_token']);
        $this->assertEquals('new-refresh', $result['refresh_token']);
        $this->assertNotNull($result['expires_at']);
    }

    public function test_refresh_token_failure_throws()
    {
        Http::fake([
            'https://provider.example/oauth/token' => Http::response(['error' => 'invalid_grant'], 400),
        ]);

        $this->expectException(\RuntimeException::class);

        $svc = new BaseRefreshOauthTokenService();
        $svc->refreshTokenFor([
            'token_url' => 'https://provider.example/oauth/token',
            'client_id' => 'cid',
            'client_secret' => 'csecret',
            'refresh_token' => 'bad-refresh',
        ]);
    }
}
