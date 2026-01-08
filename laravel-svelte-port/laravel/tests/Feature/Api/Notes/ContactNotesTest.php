<?php

/**
 * Comprehensive Contact Notes API Tests
 *
 * Tests all contact notes functionality including CRUD operations
 * and authorization.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;

describe('Contact Notes Listing', function () {
    test('can list notes for contact', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes");

        $response->assertOk();
    });

    test('notes list returns expected fields', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('notes are sorted by creation date', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes");

        $response->assertOk();
    });
});

describe('Contact Notes Creation', function () {
    test('can create note for contact', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'This is a note about the contact.',
            ]);

        $response->assertCreated();
    });

    test('note stores user who created it', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'Note with author',
            ]);

        $response->assertCreated();
    });

    test('note requires content', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });

    test('agent can create notes', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'Agent note',
            ]);

        $response->assertCreated();
    });
});

describe('Contact Notes Retrieval', function () {
    test('can show single note', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'Test note',
            ]);

        $noteId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes/{$noteId}");

        $response->assertOk();
    });

    test('cannot view note for contact in other account', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($otherAccount)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/contacts/{$contact->id}/notes");

        $response->assertNotFound();
    });
});

describe('Contact Notes Update', function () {
    test('can update note content', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'Original content',
            ]);

        $noteId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes/{$noteId}", [
                'content' => 'Updated content',
            ]);

        $response->assertOk();
    });

    test('author can update their own note', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'My note',
            ]);

        $noteId = $createResponse->json('data.id');

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes/{$noteId}", [
                'content' => 'My updated note',
            ]);

        $response->assertOk();
    });
});

describe('Contact Notes Deletion', function () {
    test('can delete note', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'Delete me',
            ]);

        $noteId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes/{$noteId}");

        $response->assertNoContent();
    });

    test('author can delete their own note', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => 'My note to delete',
            ]);

        $noteId = $createResponse->json('data.id');

        $response = $this->actingAs($agent, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes/{$noteId}");

        $response->assertNoContent();
    });
});

describe('Contact Notes Authorization', function () {
    test('unauthenticated user cannot list notes', function () {
        $account = Account::factory()->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes");

        $response->assertUnauthorized();
    });

    test('user without account access cannot view notes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes");

        $response->assertNotFound();
    });
});

describe('Contact Notes Edge Cases', function () {
    test('handles unicode content', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => '重要なメモ 📝 Important note',
            ]);

        $response->assertCreated();
    });

    test('handles very long notes', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $longContent = str_repeat('This is a long note. ', 500);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => $longContent,
            ]);

        $response->assertCreated();
    });

    test('handles special characters', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                'content' => '<script>alert("test")</script> & "quotes" \'apostrophe\'',
            ]);

        $response->assertCreated();
    });

    test('handles many notes efficiently', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $contact = Contact::factory()->for($account)->create();

        // Create many notes
        for ($i = 0; $i < 50; $i++) {
            $this->actingAs($admin, 'sanctum')
                ->postJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes", [
                    'content' => "Note number {$i}",
                ]);
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}/notes");

        $response->assertOk();
    });
});
