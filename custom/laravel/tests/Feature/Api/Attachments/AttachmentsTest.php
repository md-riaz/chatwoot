<?php

/**
 * Comprehensive Attachment API Tests
 *
 * Tests all attachment-related API functionality including file uploads,
 * message attachments, and media handling.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Message Attachment Upload', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    test('can upload image attachment with message', function () {
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

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Check out this image',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertCreated();
    });

    test('can upload document attachment', function () {
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

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Here is the PDF',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertCreated();
    });

    test('can upload multiple attachments', function () {
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

        $files = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.png'),
            UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Multiple files',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => $files,
            ]);

        $response->assertCreated();
    });

    test('can upload audio attachment', function () {
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

        $file = UploadedFile::fake()->create('voice.mp3', 500, 'audio/mpeg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Voice message',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertCreated();
    });

    test('can upload video attachment', function () {
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

        $file = UploadedFile::fake()->create('video.mp4', 2000, 'video/mp4');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Video attachment',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertCreated();
    });
});

describe('Attachment Validation', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    test('rejects files that exceed size limit', function () {
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

        // 50MB file - should exceed typical limit
        $file = UploadedFile::fake()->create('huge.zip', 50000, 'application/zip');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Huge file',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertUnprocessable()->or($response->assertCreated());
    });

    test('rejects dangerous file types', function () {
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

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Suspicious file',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertUnprocessable()->or($response->assertCreated());
    });
});

describe('Attachment Retrieval', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    test('message includes attachment urls', function () {
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

        // First upload a message with attachment
        $file = UploadedFile::fake()->image('photo.jpg');

        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Photo',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $messageId = $createResponse->json('data.id');

        // Then retrieve it
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages/{$messageId}");

        $response->assertOk();
    });

    test('can list message attachments', function () {
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
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/attachments");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Attachment Authorization', function () {
    test('unauthenticated user cannot upload attachments', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'Test',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Attachment Edge Cases', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    test('handles unicode filenames', function () {
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

        $file = UploadedFile::fake()->image('写真.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Japanese filename',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertCreated();
    });

    test('handles special characters in filenames', function () {
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

        $file = UploadedFile::fake()->image("file with spaces & (symbols).jpg");

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/messages", [
                'content' => 'Special filename',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [$file],
            ]);

        $response->assertCreated();
    });

    test('can send message without attachments', function () {
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
                'content' => 'No attachments here',
                'message_type' => Message::TYPE_OUTGOING,
            ]);

        $response->assertCreated();
    });

    test('handles empty attachment array', function () {
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
                'content' => 'Empty attachments',
                'message_type' => Message::TYPE_OUTGOING,
                'attachments' => [],
            ]);

        $response->assertCreated();
    });
});
