<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('Facebook channel parity', function () {
    function createFacebookAdminContext(): array
    {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => 1]);

        return [$account, $admin];
    }

    test('can initiate facebook authorization', function () {
        [$account, $admin] = createFacebookAdminContext();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/callbacks/facebook/initiateAuthorization");

        $response->assertOk();
        $response->assertJsonStructure(['authorization_url']);
    });

    test('can list facebook pages from graph api', function () {
        [$account, $admin] = createFacebookAdminContext();

        Http::fake([
            'https://graph.facebook.com/*/me/accounts*' => Http::response([
                'data' => [
                    [
                        'id' => '123456789',
                        'name' => 'Acme Support',
                        'access_token' => 'page_token',
                        'instagram_business_account' => ['id' => 'ig_123'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/channels/facebook/pages?user_access_token=user_token");

        $response->assertOk();
        $response->assertJsonPath('data.0.id', '123456789');
        $response->assertJsonPath('data.0.name', 'Acme Support');
        $response->assertJsonPath('data.0.page_access_token', 'page_token');
        $response->assertJsonPath('data.0.user_access_token', 'user_token');
        $response->assertJsonPath('data.0.instagram_id', 'ig_123');
        $response->assertJsonPath('data.0.exists', false);
    });

    test('can create facebook inbox through channel endpoint', function () {
        [$account, $admin] = createFacebookAdminContext();

        Http::fake([
            'https://graph.facebook.com/*/123456789/subscribed_apps' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/channels/facebook", [
                'name' => 'Facebook Page',
                'page_id' => '123456789',
                'page_access_token' => 'page_token',
                'user_access_token' => 'user_token',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Facebook Page');
        $response->assertJsonPath('data.channel_type', 'Channel::FacebookPage');

        $this->assertDatabaseHas('channel_facebook_pages', [
            'account_id' => $account->id,
            'page_id' => '123456789',
            'user_access_token' => 'user_token',
        ]);

        $this->assertDatabaseHas('inboxes', [
            'account_id' => $account->id,
            'name' => 'Facebook Page',
            'channel_type' => 'Channel::FacebookPage',
        ]);
    });

    test('marks pages that already have an inbox', function () {
        [$account, $admin] = createFacebookAdminContext();

        $channel = \App\Models\Channels\FacebookPage::create([
            'account_id' => $account->id,
            'page_id' => '123456789',
            'page_access_token' => 'existing_page_token',
            'user_access_token' => 'existing_user_token',
        ]);

        \App\Models\Inbox::create([
            'account_id' => $account->id,
            'name' => 'Existing Facebook Inbox',
            'channel_type' => 'Channel::FacebookPage',
            'channel_id' => $channel->id,
        ]);

        Http::fake([
            'https://graph.facebook.com/*/me/accounts*' => Http::response([
                'data' => [
                    [
                        'id' => '123456789',
                        'name' => 'Existing Page',
                        'access_token' => 'page_token',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/channels/facebook/pages?user_access_token=user_token");

        $response->assertOk();
        $response->assertJsonPath('data.0.exists', true);
    });
});
