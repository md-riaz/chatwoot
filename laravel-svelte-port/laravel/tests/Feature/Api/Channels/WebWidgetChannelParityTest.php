<?php

use App\Models\Account;
use App\Models\Channels\WebWidget;
use App\Models\Inbox;
use App\Models\User;

describe('Web widget channel parity', function () {
    function createWebWidgetAdminContext(): array
    {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => 1]);

        return [$account, $admin];
    }

    test('can create web widget inbox through channel endpoint', function () {
        [$account, $admin] = createWebWidgetAdminContext();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/channels/web_widget", [
                'name' => 'Website Support',
                'website_url' => 'https://example.com',
                'widget_color' => '#0f172a',
                'welcome_title' => 'Chat with us',
                'welcome_tagline' => 'We usually reply within a few minutes.',
                'greeting_enabled' => true,
                'greeting_message' => 'Welcome to support.',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Website Support');
        $response->assertJsonPath('data.channel_type', 'Channel::WebWidget');
        $response->assertJsonPath('data.channel.website_url', 'https://example.com');
        $response->assertJsonPath('data.channel.widget_color', '#0f172a');
    });

    test('can update widget settings through channel endpoint', function () {
        [$account, $admin] = createWebWidgetAdminContext();

        $channel = WebWidget::factory()->create([
            'account_id' => $account->id,
            'website_url' => 'https://example.com',
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => 'Website Support',
            'channel_type' => 'Channel::WebWidget',
            'channel_id' => $channel->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/channels/web_widget/{$inbox->id}", [
                'name' => 'Support Widget',
                'website_url' => 'https://chatwoot.com',
                'widget_color' => '#1d4ed8',
                'welcome_title' => 'Need help?',
                'welcome_tagline' => 'Our team is online.',
                'pre_chat_form_enabled' => true,
                'pre_chat_form_options' => [
                    'pre_chat_message' => 'Before we connect you, share your details.',
                    'require_email' => true,
                    'require_name' => true,
                    'require_phone_number' => false,
                ],
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Support Widget');
        $response->assertJsonPath('data.channel.website_url', 'https://chatwoot.com');
        $response->assertJsonPath('data.channel.pre_chat_form_enabled', true);

        expect($channel->fresh()->widget_color)->toBe('#1d4ed8');
        expect($channel->fresh()->pre_chat_form_options['pre_chat_message'])
            ->toBe('Before we connect you, share your details.');
    });

    test('can fetch web widget embed script', function () {
        [$account, $admin] = createWebWidgetAdminContext();

        $channel = WebWidget::factory()->create([
            'account_id' => $account->id,
            'website_token' => 'widget-token-123',
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => 'Website Support',
            'channel_type' => 'Channel::WebWidget',
            'channel_id' => $channel->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/channels/web_widget/{$inbox->id}/script");

        $response->assertOk();
        $response->assertJsonPath('data.script', fn (string $script) => str_contains($script, 'widget-token-123'));
    });
});
