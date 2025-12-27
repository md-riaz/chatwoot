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
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJsonPath('has_draft', false);
    });
});

describe('Draft Messages Update', function () {
    test('can save draft message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'This is a draft message',
                ],
            ]);

        $response->assertOk();
    });

    test('can retrieve saved draft', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

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
            ->assertJsonPath('message', 'This is a draft message');
    });
});

describe('Draft Messages Delete', function () {
    test('can delete draft message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

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
});
