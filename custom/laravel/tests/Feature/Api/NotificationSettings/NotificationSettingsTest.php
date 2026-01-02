<?php

/**
 * Notification Settings API Tests
 *
 * Tests notification settings show/update functionality.
 */

use App\Models\Account;
use App\Models\NotificationSetting;
use App\Models\User;

describe('Notification Settings Show', function () {
    test('can show notification settings for user', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notification_settings");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'selected_email_flags',
                    'selected_push_flags',
                ],
            ]);
    });

    test('creates default settings if none exist', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        expect(NotificationSetting::where('user_id', $user->id)->count())->toBe(0);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notification_settings");

        $response->assertOk();
        expect(NotificationSetting::where('user_id', $user->id)->count())->toBe(1);
    });
});

describe('Notification Settings Update', function () {
    test('can update notification settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notification_settings", [
                'notification_settings' => [
                    'selected_email_flags' => ['conversation_creation', 'conversation_assignment'],
                    'selected_push_flags' => ['conversation_mention'],
                ],
            ]);

        $response->assertOk();
    });
});

describe('Notification Settings Authorization', function () {
    test('unauthenticated user cannot access notification settings', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/notification_settings");

        $response->assertUnauthorized();
    });
});
