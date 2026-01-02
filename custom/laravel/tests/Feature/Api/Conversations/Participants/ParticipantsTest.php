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
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        ConversationParticipant::factory()->for($conversation)->for($user)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants");

        $response->assertOk();
    });
});

describe('Conversation Participants Creation', function () {
    test('can add participants to conversation', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($user2->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertOk();
    });
});

describe('Conversation Participants Update', function () {
    test('can update participants for conversation', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($user2->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user->id, $user2->id],
            ]);

        $response->assertOk();
    });
});

describe('Conversation Participants Deletion', function () {
    test('can remove participants from conversation', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($user2->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        ConversationParticipant::factory()->for($conversation)->for($user2)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/participants", [
                'user_ids' => [$user2->id],
            ]);

        $response->assertNoContent();
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
});
