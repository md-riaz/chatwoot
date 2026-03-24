<?php

use App\Models\Account;
use App\Models\Channels\Email;
use App\Models\Inbox;
use App\Models\User;

describe('Email channel parity', function () {
    function createEmailAdminContext(): array
    {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => 1]);

        return [$account, $admin];
    }

    test('can create email inbox through channel endpoint', function () {
        [$account, $admin] = createEmailAdminContext();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/channels/email", [
                'name' => 'Support Email',
                'email' => 'support@example.com',
                'imap_enabled' => false,
                'smtp_enabled' => false,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Support Email');
        $response->assertJsonPath('data.channel_type', 'Channel::Email');
    });

    test('can update email imap and smtp settings through channel endpoint', function () {
        [$account, $admin] = createEmailAdminContext();

        $channel = Email::create([
            'account_id' => $account->id,
            'email' => 'support@example.com',
            'forward_to_email' => 'support@example.com',
            'imap_enabled' => false,
            'smtp_enabled' => false,
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => 'Support Email',
            'channel_type' => 'Channel::Email',
            'channel_id' => $channel->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/channels/email/{$inbox->id}", [
                'imap_enabled' => true,
                'imap_address' => 'imap.example.com',
                'imap_port' => 993,
                'imap_login' => 'support@example.com',
                'imap_password' => 'secret',
                'imap_enable_ssl' => true,
                'smtp_enabled' => true,
                'smtp_address' => 'smtp.example.com',
                'smtp_port' => 587,
                'smtp_login' => 'support@example.com',
                'smtp_password' => 'secret',
                'smtp_domain' => 'example.com',
                'smtp_enable_ssl_tls' => false,
                'smtp_enable_starttls_auto' => true,
                'smtp_authentication' => 'login',
                'smtp_openssl_verify_mode' => 'peer',
            ]);

        $response->assertOk();
        expect($channel->fresh()->imap_enabled)->toBeTrue();
        expect($channel->fresh()->smtp_enabled)->toBeTrue();
        expect($channel->fresh()->smtp_domain)->toBe('example.com');
    });
});
