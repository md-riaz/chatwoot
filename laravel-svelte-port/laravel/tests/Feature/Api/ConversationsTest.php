<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

test('can list conversations for account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();

    Conversation::factory(5)
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/accounts/{$account->id}/conversations");

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

test('can create conversation', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/accounts/{$account->id}/conversations", [
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.inbox_id', $inbox->id)
        ->assertJsonPath('data.contact_id', $contact->id);
});

test('can show conversation', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $conversation->id);
});

test('can assign conversation', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);
    $account->users()->attach($assignee->id, ['role' =>  0]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create(['assignee_id' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/assign", [
            'assignee_id' => $assignee->id,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.assignee_id', $assignee->id);
});

test('can resolve conversation', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->open()
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/resolve");

    $response->assertOk()
        ->assertJsonPath('data.status', Conversation::STATUS_RESOLVED);
});

test('cannot access conversations from other accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $otherAccount = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $inbox = Inbox::factory()->for($otherAccount)->create();
    $contact = Contact::factory()->for($otherAccount)->create();
    $conversation = Conversation::factory()
        ->for($otherAccount)
        ->for($inbox)
        ->for($contact)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/accounts/{$otherAccount->id}/conversations/{$conversation->id}");

    // User should not have access to other account
    $response->assertNotFound();
});
