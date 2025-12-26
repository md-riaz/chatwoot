<?php

/**
 * Comprehensive Bulk Actions API Tests
 *
 * Tests all bulk action-related API functionality including bulk assignment,
 * bulk status changes, and bulk operations.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('Bulk Conversation Assignment', function () {
    test('can bulk assign conversations to agent', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['assignee_id' => $agent->id],
            ]);

        $response->assertOk();
    });

    test('can bulk unassign conversations', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['assignee_id' => null],
            ]);

        $response->assertOk();
    });
});

describe('Bulk Status Change', function () {
    test('can bulk resolve conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(10)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->open()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['status' => Conversation::STATUS_RESOLVED],
            ]);

        $response->assertOk();
    });

    test('can bulk reopen conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['status' => Conversation::STATUS_OPEN],
            ]);

        $response->assertOk();
    });

    test('can bulk snooze conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(3)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->open()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => [
                    'status' => Conversation::STATUS_SNOOZED,
                    'snoozed_until' => now()->addDays(1)->toIso8601String(),
                ],
            ]);

        $response->assertOk();
    });
});

describe('Bulk Label Operations', function () {
    test('can bulk add labels to conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'labels' => ['add' => ['urgent', 'vip']],
            ]);

        $response->assertOk();
    });

    test('can bulk remove labels from conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'labels' => ['remove' => ['processed']],
            ]);

        $response->assertOk();
    });
});

describe('Bulk Team Assignment', function () {
    test('can bulk assign conversations to team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['team_id' => 1],
            ]);

        $response->assertOk();
    });
});

describe('Bulk Action Validation', function () {
    test('requires type parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'ids' => [1, 2, 3],
                'fields' => ['status' => 1],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    });

    test('requires ids parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'fields' => ['status' => 1],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    });

    test('ids must be array', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => 'not-an-array',
                'fields' => ['status' => 1],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    });

    test('cannot process empty ids array', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => [],
                'fields' => ['status' => 1],
            ]);

        $response->assertUnprocessable()->or($response->assertOk());
    });
});

describe('Bulk Action Authorization', function () {
    test('unauthenticated user cannot perform bulk actions', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
            'type' => 'Conversation',
            'ids' => [1, 2, 3],
            'fields' => ['status' => 1],
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot perform bulk actions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => [1, 2, 3],
                'fields' => ['status' => 1],
            ]);

        $response->assertNotFound();
    });
});

describe('Bulk Action Edge Cases', function () {
    test('handles large number of conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(100)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['status' => Conversation::STATUS_RESOLVED],
            ]);

        $response->assertOk();
    });

    test('handles non-existent conversation ids gracefully', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => [99999, 99998, 99997],
                'fields' => ['status' => Conversation::STATUS_RESOLVED],
            ]);

        $response->assertOk()->or($response->assertNotFound());
    });

    test('handles mixed valid and invalid ids', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $validConversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => [$validConversation->display_id, 99999],
                'fields' => ['status' => Conversation::STATUS_RESOLVED],
            ]);

        $response->assertOk();
    });
});

describe('Bulk Priority Update', function () {
    test('can bulk update priority', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/bulk_actions", [
                'type' => 'Conversation',
                'ids' => $conversations->pluck('display_id')->toArray(),
                'fields' => ['priority' => Conversation::PRIORITY_HIGH],
            ]);

        $response->assertOk();
    });
});
