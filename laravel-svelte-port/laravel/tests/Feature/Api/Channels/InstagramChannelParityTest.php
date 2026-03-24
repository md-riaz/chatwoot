<?php

use App\Models\Account;
use App\Models\Channels\Instagram;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

describe('Instagram channel parity', function () {
    function createInstagramAdminContext(): array
    {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => 1]);

        return [$account, $admin];
    }

    test('can initiate instagram authorization', function () {
        [$account, $admin] = createInstagramAdminContext();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/channels/instagram/initiateAuthorization");

        $response->assertOk();
        $response->assertJsonStructure(['url']);

        $authorizationUrl = $response->json('url');
        parse_str(parse_url($authorizationUrl, PHP_URL_QUERY) ?: '', $query);

        expect($query['state'] ?? null)->not->toBeNull();
        expect(Cache::get("instagram_oauth_state:{$query['state']}"))->toMatchArray([
            'account_id' => $account->id,
        ]);
    });

    test('instagram callback creates inbox and redirects to agents for new account', function () {
        [$account] = createInstagramAdminContext();

        Cache::put('instagram_oauth_state:teststate', [
            'account_id' => $account->id,
        ], now()->addMinutes(10));

        Http::fake([
            'https://graph.instagram.com/oauth/access_token' => Http::response([
                'access_token' => 'short_token',
            ], 200),
            'https://graph.instagram.com/*/access_token*' => Http::response([
                'access_token' => 'long_token',
                'expires_in' => 5184000,
            ], 200),
            'https://graph.instagram.com/*/me*' => Http::response([
                'user_id' => 'ig_123',
                'username' => 'acme.ig',
            ], 200),
            'https://graph.instagram.com/v22.0/ig_123/subscribed_apps' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = $this->get('/auth/instagram/callback?state=teststate&code=oauth-code');

        $inbox = Inbox::where('account_id', $account->id)
            ->where('channel_type', 'Channel::Instagram')
            ->first();

        expect($inbox)->not->toBeNull();
        $response->assertRedirect("/app/accounts/{$account->id}/settings/inboxes/new/{$inbox->id}/agents");
    });

    test('instagram callback updates existing inbox and redirects to configuration', function () {
        [$account] = createInstagramAdminContext();

        $channel = Instagram::create([
            'account_id' => $account->id,
            'instagram_id' => 'ig_123',
            'access_token' => 'old_token',
            'expires_at' => now()->subDay(),
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => 'old.name',
            'channel_type' => 'Channel::Instagram',
            'channel_id' => $channel->id,
        ]);

        Cache::put('instagram_oauth_state:teststate', [
            'account_id' => $account->id,
        ], now()->addMinutes(10));

        Http::fake([
            'https://graph.instagram.com/oauth/access_token' => Http::response([
                'access_token' => 'short_token',
            ], 200),
            'https://graph.instagram.com/*/access_token*' => Http::response([
                'access_token' => 'long_token',
                'expires_in' => 5184000,
            ], 200),
            'https://graph.instagram.com/*/me*' => Http::response([
                'user_id' => 'ig_123',
                'username' => 'new.name',
            ], 200),
        ]);

        $response = $this->get('/auth/instagram/callback?state=teststate&code=oauth-code');

        $response->assertRedirect("/app/accounts/{$account->id}/settings/inboxes/{$inbox->id}/configuration");

        expect($channel->fresh()->access_token)->toBe('long_token');
        expect($inbox->fresh()->name)->toBe('new.name');
    });
});
