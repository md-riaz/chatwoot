<?php

namespace Tests\Unit\Services;

use App\Models\Integration;
use App\Services\Integrations\SlackService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlackServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_tokens_if_needed_updates_integration()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is not available in this environment');
        }

        $integration = Integration::factory()->create([
            'type' => 'slack',
            'credentials' => [
                'refresh_token' => 'old-refresh',
                'client_id' => 'cid',
                'client_secret' => 'csecret',
                'token_url' => 'https://provider.example/oauth/token',
            ],
        ]);

        Http::fake([
            'https://provider.example/oauth/token' => Http::response([
                'access_token' => 'new-access',
                'refresh_token' => 'new-refresh',
                'expires_in' => 3600,
            ], 200),
        ]);

        $svc = new SlackService($integration);

        $this->assertTrue($svc->refreshTokensIfNeeded($integration));

        $integration->refresh();
        $creds = $integration->credentials;

        $this->assertEquals('new-access', $creds['bot_token']);
        $this->assertEquals('new-refresh', $creds['refresh_token']);
    }
}
