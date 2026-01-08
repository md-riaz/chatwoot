<?php

/**
 * User Profile API Tests
 *
 * Tests user profile management functionality including
 * profile updates, password changes, and notification settings.
 */

use App\Models\Account;
use App\Models\User;

describe('Profile Retrieval', function () {
    test('can get current user profile', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonPath('data.name', 'Test User')
            ->assertJsonPath('data.email', 'test@example.com');
    });

    test('profile includes accounts', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile');

        $response->assertOk();
    });

    test('unauthenticated user cannot access profile', function () {
        $response = $this->getJson('/api/v1/profile');

        $response->assertUnauthorized();
    });
});

describe('Profile Update', function () {
    test('can update profile name', function () {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'name' => 'New Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    });

    test('can update display name', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'display_name' => 'Johnny',
            ]);

        $response->assertOk();
    });

    test('cannot update email to existing email', function () {
        User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'current@example.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'email' => 'existing@example.com',
            ]);

        $response->assertUnprocessable();
    });

    test('can update avatar', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'avatar_url' => 'https://example.com/avatar.jpg',
            ]);

        $response->assertOk();
    });
});

describe('Password Change', function () {
    test('can change password', function () {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/password', [
                'current_password' => 'old_password',
                'password' => 'new_password123',
                'password_confirmation' => 'new_password123',
            ]);

        $response->assertOk();
    });

    test('password change requires current password', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/password', [
                'password' => 'new_password123',
                'password_confirmation' => 'new_password123',
            ]);

        $response->assertUnprocessable();
    });

    test('password change requires confirmation', function () {
        $user = User::factory()->create([
            'password' => bcrypt('current_password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/password', [
                'current_password' => 'current_password',
                'password' => 'new_password123',
            ]);

        $response->assertUnprocessable();
    });

    test('password must meet minimum length', function () {
        $user = User::factory()->create([
            'password' => bcrypt('current_password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/password', [
                'current_password' => 'current_password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertUnprocessable();
    });
});

describe('Notification Settings', function () {
    test('can get notification settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notification_settings");

        $response->assertOk();
    });

    test('can update notification settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notification_settings", [
                'email_conversation_creation' => false,
                'email_conversation_assignment' => true,
                'push_conversation_creation' => true,
            ]);

        $response->assertOk();
    });

    test('can enable all notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notification_settings", [
                'all_notifications' => true,
            ]);

        $response->assertOk();
    });

    test('can disable all notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notification_settings", [
                'all_notifications' => false,
            ]);

        $response->assertOk();
    });
});

describe('Availability Status', function () {
    test('can set availability to online', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile/availability', [
                'availability' => 1, // online
            ]);

        $response->assertOk();
    });

    test('can set availability to busy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile/availability', [
                'availability' => 2, // busy
            ]);

        $response->assertOk();
    });

    test('can set availability to offline', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile/availability', [
                'availability' => 0, // offline
            ]);

        $response->assertOk();
    });

    test('can set auto-offline timeout', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'auto_offline_timeout' => 30, // minutes
            ]);

        $response->assertOk();
    });
});

describe('Push Notification Subscriptions', function () {
    test('can add push subscription', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/push_subscriptions', [
                'subscription' => [
                    'endpoint' => 'https://push.example.com/send/123',
                    'keys' => [
                        'p256dh' => 'test_key',
                        'auth' => 'test_auth',
                    ],
                ],
            ]);

        $response->assertCreated();
    });

    test('can remove push subscription', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/profile/push_subscriptions', [
                'endpoint' => 'https://push.example.com/send/123',
            ]);

        $response->assertNoContent();
    });
});

describe('Access Tokens', function () {
    test('can list access tokens', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile/tokens');

        $response->assertOk();
    });

    test('can create access token', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/profile/tokens', [
                'name' => 'API Access Token',
            ]);

        $response->assertCreated();
    });

    test('can revoke access token', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test_token');

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/profile/tokens/{$token->accessToken->id}");

        $response->assertNoContent();
    });
});

describe('Profile Edge Cases', function () {
    test('handles unicode in profile name', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'name' => '田中太郎 🧑‍💻',
            ]);

        $response->assertOk();
    });

    test('handles special characters in name', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'name' => "O'Connor-Smith",
            ]);

        $response->assertOk();
    });
});
