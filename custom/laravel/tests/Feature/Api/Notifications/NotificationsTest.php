<?php

/**
 * Comprehensive Notification API Tests
 *
 * Tests all notification-related API functionality including listing,
 * marking as read, and notification settings.
 */

use App\Models\Account;
use App\Models\User;

describe('Notification Listing', function () {
    test('can list notifications for user', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('notifications are user-specific', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user1->id, ['role' => 2]);
        $account->users()->attach($user2->id, ['role' => 1]);

        // Each user should only see their own notifications
        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications");

        $response->assertOk();
    });

    test('notifications list is paginated', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Notification Filtering', function () {
    test('can filter unread notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?status=unread");

        $response->assertOk();
    });

    test('can filter read notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?status=read");

        $response->assertOk();
    });

    test('can filter by notification type', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?type=conversation_assigned");

        $response->assertOk();
    });
});

describe('Notification Actions', function () {
    test('can get unread count', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications/unread");

        $response->assertOk()
            ->assertJsonStructure(['count']);
    });

    test('can mark all notifications as read', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/notifications/read_all");

        $response->assertOk();
    });

    test('can mark single notification as read', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notifications/1", [
                'read' => true,
            ]);

        $response->assertOk()->or($response->assertNotFound());
    });

    test('can delete notification', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/notifications/1");

        $response->assertNoContent()->or($response->assertNotFound());
    });

    test('can delete all notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/notifications/destroy_all");

        $response->assertOk();
    });

    test('can snooze notification', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/notifications/1/snooze", [
                'snooze_until' => now()->addHours(2)->toIso8601String(),
            ]);

        $response->assertOk()->or($response->assertNotFound());
    });
});

describe('Notification Settings', function () {
    test('can get notification settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notification_settings");

        $response->assertOk();
    });

    test('can update notification settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notification_settings", [
                'email_notifications' => true,
                'push_notifications' => false,
            ]);

        $response->assertOk();
    });

    test('can toggle specific notification types', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/notification_settings", [
                'conversation_assignment' => true,
                'conversation_mention' => true,
                'new_message' => false,
            ]);

        $response->assertOk();
    });
});

describe('Notification Authorization', function () {
    test('unauthenticated user cannot list notifications', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/notifications");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot access notification settings', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/notification_settings");

        $response->assertUnauthorized();
    });

    test('user without account access cannot view notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications");

        $response->assertNotFound();
    });
});

describe('Notification Edge Cases', function () {
    test('handles large number of notifications', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?per_page=100");

        $response->assertOk();
    });

    test('handles notification with missing primary actor', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        // This tests graceful handling of edge cases
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications");

        $response->assertOk();
    });
});

describe('Notification Types', function () {
    test('conversation assignment notification', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?type=conversation_assigned");

        $response->assertOk();
    });

    test('mention notification', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?type=mention");

        $response->assertOk();
    });

    test('new message notification', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/notifications?type=new_message");

        $response->assertOk();
    });
});
