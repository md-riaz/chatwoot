<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('Contact Conversations API', function () {
    test('can list conversations for a contact', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]); // Agent role

        $contact = Contact::factory()->for($account)->create();
        $inbox = Inbox::factory()->for($account)->create();

        // Create 5 conversations for this contact
        Conversation::factory(5)->create([
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'inbox_id' => $inbox->id,
        ]);

        // Create a conversation for another contact in the same account
        $otherContact = Contact::factory()->for($account)->create();
        Conversation::factory()->create([
            'account_id' => $account->id,
            'contact_id' => $otherContact->id,
            'inbox_id' => $inbox->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/conversations");

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'contact',
                        'inbox',
                    ],
                ],
            ]);
    });

    test('returns 404 for contact in another account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $otherAccount = Account::factory()->create();
        $contact = Contact::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/conversations");

        $response->assertNotFound();
    });

    test('unauthenticated user cannot list conversations', function () {
        $account = Account::factory()->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/conversations");

        $response->assertUnauthorized();
    });
});
