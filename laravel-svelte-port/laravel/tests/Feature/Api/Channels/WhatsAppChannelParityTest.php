<?php

use App\Models\Account;
use App\Models\Channels\Whatsapp;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('WhatsApp channel parity', function () {
    function createWhatsAppAdminContext(): array
    {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => 1]);

        return [$account, $admin];
    }

    test('can create whatsapp cloud inbox through channel endpoint', function () {
        [$account, $admin] = createWhatsAppAdminContext();

        Http::fake([
            'https://graph.facebook.com/*/123456789' => Http::response([
                'code_verification_status' => 'VERIFIED',
            ], 200),
            'https://graph.facebook.com/*/987654321/subscribed_apps' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/channels/whatsapp", [
                'name' => 'WhatsApp Support',
                'phone_number' => '+15551234567',
                'provider' => 'whatsapp_cloud',
                'provider_config' => [
                    'phone_number_id' => '123456789',
                    'business_account_id' => '987654321',
                    'api_key' => 'meta_token',
                ],
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'WhatsApp Support');
        $response->assertJsonPath('data.channel_type', 'Channel::Whatsapp');

        $channel = Whatsapp::first();

        expect($channel)->not->toBeNull();
        expect($channel->provider)->toBe('whatsapp_cloud');
        expect($channel->provider_config['phone_number_id'])->toBe('123456789');
        expect($channel->provider_config['business_account_id'])->toBe('987654321');
        expect($channel->provider_config['api_key'])->toBe('meta_token');
        expect($channel->provider_config['webhook_verify_token'] ?? null)->not->toBeNull();

        $this->assertDatabaseHas('inboxes', [
            'account_id' => $account->id,
            'name' => 'WhatsApp Support',
            'channel_type' => 'Channel::Whatsapp',
            'channel_id' => $channel->id,
        ]);
    });

    test('prevents duplicate whatsapp inboxes for the same phone number', function () {
        [$account, $admin] = createWhatsAppAdminContext();

        $channel = Whatsapp::create([
            'account_id' => $account->id,
            'phone_number' => '+15551234567',
            'provider' => 'whatsapp_cloud',
            'provider_config' => [
                'phone_number_id' => '123456789',
                'business_account_id' => '987654321',
                'api_key' => 'meta_token',
                'webhook_verify_token' => 'verify-token',
            ],
        ]);

        Inbox::create([
            'account_id' => $account->id,
            'name' => 'Existing WhatsApp',
            'channel_type' => 'Channel::Whatsapp',
            'channel_id' => $channel->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/channels/whatsapp", [
                'name' => 'WhatsApp Support',
                'phone_number' => '+15551234567',
                'provider' => 'whatsapp_cloud',
                'provider_config' => [
                    'phone_number_id' => '123456789',
                    'business_account_id' => '987654321',
                    'api_key' => 'meta_token',
                ],
            ]);

        $response->assertStatus(422);
    });
});
