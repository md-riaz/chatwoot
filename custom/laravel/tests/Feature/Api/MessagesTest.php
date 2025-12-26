<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\User;

test('can list messages for conversation', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' => 2]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create();

    Message::factory(10)
        ->for($account)
        ->for($conversation)
        ->for($inbox)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

    $response->assertOk()
        ->assertJsonCount(10, 'data');
});

test('can create message', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' => 2]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'Hello, this is a test message',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'Hello, this is a test message');
});

test('can create private note', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' => 2]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'This is a private note',
            'private' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.private', true);
});

test('message creation updates conversation last_activity_at', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' => 2]);

    $inbox = Inbox::factory()->for($account)->create();
    $contact = Contact::factory()->for($account)->create();
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->create(['last_activity_at' => now()->subHours(2)]);

    $oldActivityTime = $conversation->last_activity_at;

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'New message',
        ]);

    $conversation->refresh();
    expect($conversation->last_activity_at)->toBeGreaterThan($oldActivityTime);
});
