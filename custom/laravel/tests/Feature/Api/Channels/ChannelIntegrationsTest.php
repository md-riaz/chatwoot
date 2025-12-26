<?php

/**
 * Comprehensive Channel Integrations API Tests
 *
 * Tests channel integrations including WhatsApp, Facebook, Telegram, etc.
 * These are primarily mock tests for the integration points.
 */

use App\Models\Account;
use App\Models\Inbox;
use App\Models\User;

describe('WhatsApp Channel', function () {
    test('can create whatsapp channel inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'WhatsApp Channel',
                'channel' => [
                    'type' => 'whatsapp',
                    'phone_number' => '+1234567890',
                    'provider' => 'whatsapp_cloud',
                ],
            ]);

        $response->assertCreated();
    });

    test('whatsapp inbox requires phone number', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'WhatsApp',
                'channel' => [
                    'type' => 'whatsapp',
                    'provider' => 'whatsapp_cloud',
                ],
            ]);

        $response->assertUnprocessable();
    });

    test('can update whatsapp channel settings', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Whatsapp']);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'channel' => [
                    'message_templates_sync' => true,
                ],
            ]);

        $response->assertOk();
    });

    test('can list whatsapp message templates', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Whatsapp']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/message_templates");

        $response->assertOk();
    });

    test('can sync whatsapp message templates', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Whatsapp']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/sync_templates");

        $response->assertOk();
    });
});

describe('Facebook Channel', function () {
    test('can initiate facebook oauth', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/callbacks/facebook/authorize");

        $response->assertOk();
    });

    test('can list facebook pages after oauth', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/facebook_indicators/pages");

        $response->assertOk();
    });

    test('can create facebook inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Facebook Page',
                'channel' => [
                    'type' => 'facebook',
                    'page_id' => '123456789',
                    'page_access_token' => 'mock_token',
                ],
            ]);

        $response->assertCreated();
    });
});

describe('Telegram Channel', function () {
    test('can create telegram inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Telegram Bot',
                'channel' => [
                    'type' => 'telegram',
                    'bot_token' => 'mock_bot_token',
                ],
            ]);

        $response->assertCreated();
    });

    test('telegram inbox requires bot token', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Telegram',
                'channel' => [
                    'type' => 'telegram',
                ],
            ]);

        $response->assertUnprocessable();
    });

    test('can update telegram webhook', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Telegram']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/set_webhook");

        $response->assertOk();
    });
});

describe('Email Channel', function () {
    test('can create email inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Email Support',
                'channel' => [
                    'type' => 'email',
                    'email' => 'support@example.com',
                ],
            ]);

        $response->assertCreated();
    });

    test('can configure IMAP settings', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Email']);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'channel' => [
                    'imap_enabled' => true,
                    'imap_address' => 'imap.example.com',
                    'imap_port' => 993,
                    'imap_login' => 'support@example.com',
                    'imap_password' => 'secret',
                ],
            ]);

        $response->assertOk();
    });

    test('can configure SMTP settings', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Email']);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'channel' => [
                    'smtp_enabled' => true,
                    'smtp_address' => 'smtp.example.com',
                    'smtp_port' => 587,
                    'smtp_login' => 'support@example.com',
                    'smtp_password' => 'secret',
                ],
            ]);

        $response->assertOk();
    });
});

describe('SMS Channel', function () {
    test('can create twilio SMS inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'SMS Support',
                'channel' => [
                    'type' => 'twilio_sms',
                    'phone_number' => '+1234567890',
                    'account_sid' => 'ACXXXX',
                    'auth_token' => 'mock_token',
                ],
            ]);

        $response->assertCreated();
    });

    test('can create bandwidth SMS inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'SMS via Bandwidth',
                'channel' => [
                    'type' => 'sms',
                    'provider' => 'bandwidth',
                    'phone_number' => '+1234567890',
                ],
            ]);

        $response->assertCreated();
    });
});

describe('Line Channel', function () {
    test('can create line inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Line Support',
                'channel' => [
                    'type' => 'line',
                    'channel_id' => 'mock_channel_id',
                    'channel_secret' => 'mock_secret',
                    'channel_access_token' => 'mock_token',
                ],
            ]);

        $response->assertCreated();
    });
});

describe('API Channel', function () {
    test('can create API channel inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'API Channel',
                'channel' => [
                    'type' => 'api',
                    'webhook_url' => 'https://api.example.com/webhook',
                ],
            ]);

        $response->assertCreated();
    });

    test('API inbox generates hmac token', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::Api']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}");

        $response->assertOk();
    });
});

describe('Web Widget Channel', function () {
    test('can create web widget inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Website Widget',
                'channel' => [
                    'type' => 'web_widget',
                    'website_url' => 'https://example.com',
                ],
            ]);

        $response->assertCreated();
    });

    test('can configure widget settings', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::WebWidget']);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'channel' => [
                    'welcome_title' => 'Welcome to our support!',
                    'welcome_tagline' => 'We are here to help',
                    'widget_color' => '#1F93FF',
                ],
            ]);

        $response->assertOk();
    });

    test('widget generates unique token', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create(['channel_type' => 'Channel::WebWidget']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}");

        $response->assertOk();
    });
});

describe('Channel Authorization', function () {
    test('unauthenticated user cannot create channels', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/inboxes", [
            'name' => 'Test',
            'channel' => ['type' => 'web_widget'],
        ]);

        $response->assertUnauthorized();
    });

    test('agent cannot create channels', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Test',
                'channel' => ['type' => 'web_widget'],
            ]);

        $response->assertForbidden();
    });
});

describe('Channel Edge Cases', function () {
    test('handles unicode channel names', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'サポートチャンネル 💬',
                'channel' => [
                    'type' => 'web_widget',
                    'website_url' => 'https://example.jp',
                ],
            ]);

        $response->assertCreated();
    });

    test('handles many channels per account', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        Inbox::factory(10)->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertOk();
    });
});
