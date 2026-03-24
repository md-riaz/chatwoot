<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

describe('Facebook callback flow', function () {
    function createFacebookCallbackContext(): array
    {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => 1]);

        return [$account, $admin];
    }

    test('initiate authorization stores oauth state', function () {
        [$account, $admin] = createFacebookCallbackContext();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/callbacks/facebook/initiateAuthorization");

        $response->assertOk();

        $authorizationUrl = $response->json('authorization_url');
        parse_str(parse_url($authorizationUrl, PHP_URL_QUERY) ?: '', $query);

        expect($query['state'] ?? null)->not->toBeNull();
        expect(Cache::get("facebook_oauth_state:{$query['state']}"))->toMatchArray([
            'account_id' => $account->id,
            'user_id' => $admin->id,
        ]);
    });

    test('oauth callback exchanges code and redirects back to spa with token key', function () {
        [$account, $admin] = createFacebookCallbackContext();

        Cache::put('facebook_oauth_state:teststate', [
            'account_id' => $account->id,
            'user_id' => $admin->id,
        ], now()->addMinutes(10));

        Http::fake([
            'https://graph.facebook.com/*/oauth/access_token*' => Http::response([
                'access_token' => 'facebook_user_token',
            ], 200),
        ]);

        $response = $this->get('/auth/facebook/callback?state=teststate&code=oauth-code');

        $response->assertRedirect();

        $target = $response->headers->get('Location');
        expect($target)->toContain("/app/accounts/{$account->id}/settings/inboxes/new/facebook");
        expect($target)->toContain('facebook_auth=success');

        parse_str(parse_url($target, PHP_URL_QUERY) ?: '', $query);
        $tokenKey = $query['token_key'] ?? null;

        expect($tokenKey)->not->toBeNull();
        expect(Cache::get("facebook_oauth_token:{$tokenKey}"))->toMatchArray([
            'account_id' => $account->id,
            'user_id' => $admin->id,
            'user_access_token' => 'facebook_user_token',
        ]);
    });

    test('consume callback token returns one time user access token', function () {
        [$account, $admin] = createFacebookCallbackContext();

        Cache::put('facebook_oauth_token:testtoken', [
            'account_id' => $account->id,
            'user_id' => $admin->id,
            'user_access_token' => 'facebook_user_token',
        ], now()->addMinutes(10));

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/callbacks/facebook/token?token_key=testtoken");

        $response->assertOk();
        $response->assertJsonPath('user_access_token', 'facebook_user_token');
        expect(Cache::get('facebook_oauth_token:testtoken'))->toBeNull();
    });
});
