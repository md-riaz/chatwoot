<?php

/**
 * Comprehensive Message API Tests
 *
 * Tests all message-related API functionality including CRUD operations,
 * types, attachments, and edge cases.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\User;

describe('Message Listing', function () {
    test('can list messages for conversation', function () {
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

        Message::factory(5)
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty conversation returns empty messages list', function () {
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
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('messages list includes expected fields', function () {
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

        Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'message_type',
                        'status',
                        'private',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('messages are sorted chronologically', function () {
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

        $older = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create(['created_at' => now()->subHours(2)]);

        $newer = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create(['created_at' => now()]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        expect($ids)->toContain($older->id);
        expect($ids)->toContain($newer->id);
    });
});

describe('Message Creation', function () {
    test('can create outgoing message', function () {
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
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Hello, how can I help you?',
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.content', 'Hello, how can I help you?')
            ->assertJsonPath('data.message_type', Message::TYPE_OUTGOING);
    });

    test('can create private note', function () {
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
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'This is a private note for the team',
                'message_type' => Message::TYPE_OUTGOING,
                'private' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.private', true);
    });

    test('message creation requires content', function () {
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
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });

    test('message creation updates conversation last_activity_at', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['last_activity_at' => now()->subDays(2)]);

        $oldActivity = $conversation->last_activity_at;

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'New message',
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $conversation->refresh();
        expect($conversation->last_activity_at->isAfter($oldActivity))->toBeTrue();
    });

    test('can create message with different content types', function () {
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
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Check out this link',
                'message_type' => Message::TYPE_OUTGOING,
                'content_type' => Message::CONTENT_TEXT,
            ]);

        $response->assertCreated();
    });
});

describe('Message Retrieval', function () {
    test('can show single message', function () {
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

        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create(['content' => 'Test message content']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $message->id)
            ->assertJsonPath('data.content', 'Test message content');
    });

    test('cannot access message from other conversation', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $conversation1 = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $conversation2 = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $message = Message::factory()
            ->for($account)
            ->for($conversation2)
            ->for($inbox)
            ->create();

        // Try to access message from conversation2 via conversation1
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation1->id}/messages/{$message->id}");

        $response->assertNotFound();
    });
});

describe('Message Update', function () {
    test('can update message content', function () {
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

        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create(['content' => 'Original content']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$message->id}", [
                'content' => 'Updated content',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Updated content');
    });
});

describe('Message Deletion', function () {
    test('can delete message', function () {
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

        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertNoContent();
        expect(Message::find($message->id))->toBeNull();
    });
});

describe('Message Types', function () {
    test('incoming message type', function () {
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

        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->incoming()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('data.message_type', Message::TYPE_INCOMING);
    });

    test('outgoing message type', function () {
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

        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->outgoing()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('data.message_type', Message::TYPE_OUTGOING);
    });

    test('activity message type', function () {
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

        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->activity()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('data.message_type', Message::TYPE_ACTIVITY);
    });
});

describe('Message Authorization', function () {
    test('unauthenticated user cannot list messages', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create message', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'Test message',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Message Edge Cases', function () {
    test('message with long content', function () {
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

        $longContent = str_repeat('This is a very long message. ', 100);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => $longContent,
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $response->assertCreated();
    });

    test('message with unicode and emoji', function () {
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
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'こんにちは 👋 ¿Cómo estás?',
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.content', 'こんにちは 👋 ¿Cómo estás?');
    });

    test('handles many messages in conversation', function () {
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

        Message::factory(100)
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages");

        $response->assertOk();
    });

    test('empty content is rejected', function () {
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
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => '',
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });
});
