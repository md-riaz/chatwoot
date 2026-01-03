<?php

/**
 * Draft Messages API Tests
 *
 * Tests draft messages show/update/delete functionality.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('Draft Messages Show', function () {
    test('returns no draft when none exists', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJsonPath('has_draft', false);
    });

    test('returns user-specific draft', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user1->id, ['role' => 0]);
        $account->users()->attach($user2->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // User 1 saves a draft
        $this->actingAs($user1, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'User 1 draft message',
                ],
            ]);

        // User 2 should not see User 1's draft
        $response = $this->actingAs($user2, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJsonPath('has_draft', false);

        // User 1 should see their own draft
        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJsonPath('has_draft', true)
            ->assertJsonPath('message', 'User 1 draft message')
            ->assertJsonPath('user_id', $user1->id);
    });
});

describe('Draft Messages Update', function () {
    test('can save draft message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'This is a draft message',
                ],
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'updated_at',
            ]);
    });

    test('validates draft message content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => '', // Empty message should fail
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message.message']);
    });

    test('validates message length', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $longMessage = str_repeat('a', 10001); // Exceeds 10000 character limit

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $longMessage,
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message.message']);
    });

    test('can retrieve saved draft', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Save draft
        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'This is a draft message',
                ],
            ]);

        // Retrieve draft
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJsonPath('has_draft', true)
            ->assertJsonPath('message', 'This is a draft message')
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonStructure([
                'has_draft',
                'message',
                'updated_at',
                'user_id',
            ]);
    });

    test('handles conflict detection', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Save initial draft
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'Initial draft',
                ],
            ]);

        $initialTimestamp = $response->json('updated_at');

        // Wait a moment to ensure different timestamps
        sleep(1);

        // Save another draft (simulating another session)
        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'Updated draft',
                ],
            ]);

        // Try to save with old timestamp (should fail)
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'Conflicting draft',
                    'updated_at' => $initialTimestamp,
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message']);
    });
});

describe('Draft Messages Delete', function () {
    test('can delete draft message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Save draft first
        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'This is a draft message',
                ],
            ]);

        // Delete draft
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertNoContent();

        // Verify deleted
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJsonPath('has_draft', false);
    });

    test('deleting non-existent draft returns success', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Delete non-existent draft
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertNoContent();
    });
});

describe('Draft Messages Authorization', function () {
    test('unauthenticated user cannot access draft messages', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertUnauthorized();
    });

    test('user cannot access drafts from different account', function () {
        $user = User::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        
        // User belongs to account1 only
        $account1->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account2)->create();
        $contact = Contact::factory()->for($account2)->create();
        $conversation = Conversation::factory()->for($account2)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account2->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertNotFound();
    });
});