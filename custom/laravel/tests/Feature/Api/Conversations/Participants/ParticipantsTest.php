<?php

/**
 * Conversation Participants API Tests
 *
 * Tests conversation participants CRUD functionality.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Inbox;
use App\Models\User;

describe('Conversation Participants Show', function () {
    test('can list participants for conversation', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        ConversationParticipant::factory()->for($conversation)->for($user)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'display_name',
                    ]
                ]
            ]);
    });

    test('returns empty array when no participants', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants");

        $response->assertOk()
            ->assertJson(['data' => []]);
    });
});

describe('Conversation Participants Creation', function () {
    test('can add participants to conversation', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);
        $account->users()->attach($user2->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ]);

        // Verify participant was created
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
            'account_id' => $account->id,
        ]);
    });

    test('prevents duplicate participants', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);
        $account->users()->attach($user2->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Add participant first time
        ConversationParticipant::factory()->for($conversation)->for($user2)->for($account)->create();

        // Try to add same participant again
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertOk(); // Should still succeed (firstOrCreate behavior)
        
        // Verify only one participant record exists
        $this->assertEquals(1, ConversationParticipant::where([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ])->count());
    });

    test('validates user has inbox access', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create(); // Not attached to account
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['user_ids']);
    });
});

describe('Conversation Participants Update', function () {
    test('can update participants for conversation', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);
        $account->users()->attach($user2->id, ['role' => 0]);
        $account->users()->attach($user3->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Add initial participant
        ConversationParticipant::factory()->for($conversation)->for($user2)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user->id, $user3->id], // Replace user2 with user and user3
            ]);

        $response->assertOk();

        // Verify user2 was removed and user, user3 were added
        $this->assertDatabaseMissing('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $user3->id,
        ]);
    });
});

describe('Conversation Participants Deletion', function () {
    test('can remove participants from conversation', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);
        $account->users()->attach($user2->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        ConversationParticipant::factory()->for($conversation)->for($user2)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertOk(); // Rails returns 200, not 204

        // Verify participant was removed
        $this->assertDatabaseMissing('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);
    });

    test('handles non-existent participants gracefully', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);
        $account->users()->attach($user2->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        // Try to remove participant that doesn't exist
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertOk(); // Should succeed gracefully
    });
});

describe('Conversation Participants Authorization', function () {
    test('unauthenticated user cannot access participants', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants");

        $response->assertUnauthorized();
    });

    test('user cannot access participants from different account', function () {
        $user = User::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $account1->users()->attach($user->id, ['role' => 0]);

        $inbox = Inbox::factory()->for($account2)->create();
        $contact = Contact::factory()->for($account2)->create();
        $conversation = Conversation::factory()->for($account2)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account2->id}/conversations/{$conversation->id}/participants");

        $response->assertNotFound();
    });
});
