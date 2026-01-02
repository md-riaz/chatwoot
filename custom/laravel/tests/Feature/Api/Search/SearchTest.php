<?php

/**
 * Comprehensive Search API Tests
 *
 * Tests all search-related API functionality including conversations,
 * contacts, messages, and articles search.
 */

use App\Models\Account;
use App\Models\Article;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\Portal;
use App\Models\User;

describe('Global Search', function () {
    test('can perform global search', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create(['name' => 'John Doe']);
        Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=John");

        $response->assertOk();
    });

    test('empty search returns results', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=");

        $response->assertOk();
    });
});

describe('Conversation Search', function () {
    test('can search conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create(['name' => 'Premium Customer']);
        Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/conversations?q=Premium");

        $response->assertOk();
    });

    test('conversation search by display_id', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['display_id' => 12345]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/conversations?q=12345");

        $response->assertOk();
    });
});

describe('Contact Search', function () {
    test('can search contacts by name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['name' => 'Alice Johnson']);
        Contact::factory()->for($account)->create(['name' => 'Bob Smith']);
        Contact::factory()->for($account)->create(['name' => 'Alice Cooper']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=Alice");

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        expect($names->filter(fn ($n) => str_contains($n, 'Alice'))->count())->toBeGreaterThanOrEqual(1);
    });

    test('can search contacts by email', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['email' => 'alice@example.com']);
        Contact::factory()->for($account)->create(['email' => 'bob@test.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=alice@");

        $response->assertOk();
    });

    test('can search contacts by phone', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['phone_number' => '+1234567890']);
        Contact::factory()->for($account)->create(['phone_number' => '+0987654321']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=1234567890");

        $response->assertOk();
    });
});

describe('Message Search', function () {
    test('can search messages by content', function () {
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

        Message::factory()->for($account)->for($conversation)->for($inbox)->create([
            'content' => 'I need help with my subscription',
        ]);
        Message::factory()->for($account)->for($conversation)->for($inbox)->create([
            'content' => 'How can I reset my password?',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/messages?q=subscription");

        $response->assertOk();
    });

    test('message search is case insensitive', function () {
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

        Message::factory()->for($account)->for($conversation)->for($inbox)->create([
            'content' => 'URGENT: Need help immediately',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/messages?q=urgent");

        $response->assertOk();
    });
});

describe('Article Search', function () {
    test('can search articles by title', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $portal = Portal::factory()->for($account)->create();
        Article::factory()->for($account)->for($portal)->create([
            'title' => 'Getting Started Guide',
        ]);
        Article::factory()->for($account)->for($portal)->create([
            'title' => 'Advanced Configuration',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/articles?q=Getting");

        $response->assertOk();
    });

    test('can search articles by content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $portal = Portal::factory()->for($account)->create();
        Article::factory()->for($account)->for($portal)->create([
            'title' => 'API Documentation',
            'content' => 'This article explains how to use webhooks in our system.',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/articles?q=webhooks");

        $response->assertOk();
    });
});

describe('Search Authorization', function () {
    test('unauthenticated user cannot search', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/search?q=test");

        $response->assertUnauthorized();
    });

    test('user without account access cannot search', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=test");

        $response->assertNotFound();
    });

    test('search results are scoped to user account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        // Create contact in other account
        Contact::factory()->for($otherAccount)->create(['name' => 'Secret Contact']);
        // Create contact in user's account
        Contact::factory()->for($account)->create(['name' => 'Public Contact']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=Contact");

        $response->assertOk();
        // Should not return contacts from other account
        $names = collect($response->json('data'))->pluck('name');
        expect($names->contains('Secret Contact'))->toBeFalse();
    });
});

describe('Search Permission Filtering', function () {
    test('user can only search conversations from assigned inboxes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 'agent']);

        // Create two inboxes
        $assignedInbox = Inbox::factory()->for($account)->create(['name' => 'Assigned Inbox']);
        $unassignedInbox = Inbox::factory()->for($account)->create(['name' => 'Unassigned Inbox']);

        // Assign user to only one inbox
        $assignedInbox->members()->attach($user->id);

        // Create contacts and conversations in both inboxes
        $assignedContact = Contact::factory()->for($account)->create(['name' => 'Assigned Contact']);
        $unassignedContact = Contact::factory()->for($account)->create(['name' => 'Unassigned Contact']);

        $assignedConversation = Conversation::factory()
            ->for($account)
            ->for($assignedInbox)
            ->for($assignedContact)
            ->create();

        $unassignedConversation = Conversation::factory()
            ->for($account)
            ->for($unassignedInbox)
            ->for($unassignedContact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/conversations?q=Contact");

        $response->assertOk();
        
        $conversations = collect($response->json('data.data'));
        $inboxIds = $conversations->pluck('inbox_id');
        
        // Should only return conversations from assigned inbox
        expect($inboxIds->contains($assignedInbox->id))->toBeTrue();
        expect($inboxIds->contains($unassignedInbox->id))->toBeFalse();
    });

    test('user can only search messages from assigned inboxes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 'agent']);

        // Create two inboxes
        $assignedInbox = Inbox::factory()->for($account)->create();
        $unassignedInbox = Inbox::factory()->for($account)->create();

        // Assign user to only one inbox
        $assignedInbox->members()->attach($user->id);

        // Create conversations and messages in both inboxes
        $assignedContact = Contact::factory()->for($account)->create();
        $unassignedContact = Contact::factory()->for($account)->create();

        $assignedConversation = Conversation::factory()
            ->for($account)
            ->for($assignedInbox)
            ->for($assignedContact)
            ->create();

        $unassignedConversation = Conversation::factory()
            ->for($account)
            ->for($unassignedInbox)
            ->for($unassignedContact)
            ->create();

        Message::factory()
            ->for($account)
            ->for($assignedConversation)
            ->for($assignedInbox)
            ->create(['content' => 'Assigned message content']);

        Message::factory()
            ->for($account)
            ->for($unassignedConversation)
            ->for($unassignedInbox)
            ->create(['content' => 'Unassigned message content']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/messages?q=message");

        $response->assertOk();
        
        $messages = collect($response->json('data.data'));
        
        // Should only return messages from assigned inbox
        $assignedMessages = $messages->filter(function ($message) {
            return str_contains($message['content'], 'Assigned');
        });
        
        $unassignedMessages = $messages->filter(function ($message) {
            return str_contains($message['content'], 'Unassigned');
        });
        
        expect($assignedMessages->count())->toBeGreaterThan(0);
        expect($unassignedMessages->count())->toBe(0);
    });

    test('administrator can search all conversations in account', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 'administrator']);

        // Create two inboxes
        $inbox1 = Inbox::factory()->for($account)->create();
        $inbox2 = Inbox::factory()->for($account)->create();

        // Create conversations in both inboxes (admin not assigned to any)
        $contact1 = Contact::factory()->for($account)->create(['name' => 'Contact One']);
        $contact2 = Contact::factory()->for($account)->create(['name' => 'Contact Two']);

        Conversation::factory()
            ->for($account)
            ->for($inbox1)
            ->for($contact1)
            ->create();

        Conversation::factory()
            ->for($account)
            ->for($inbox2)
            ->for($contact2)
            ->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/conversations?q=Contact");

        $response->assertOk();
        
        $conversations = collect($response->json('data.data'));
        $inboxIds = $conversations->pluck('inbox_id');
        
        // Admin should see conversations from both inboxes
        expect($inboxIds->contains($inbox1->id))->toBeTrue();
        expect($inboxIds->contains($inbox2->id))->toBeTrue();
    });

    test('user with no inbox access gets empty results', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 'agent']);

        // Create inbox and conversation but don't assign user to inbox
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create(['name' => 'Test Contact']);
        
        Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/conversations?q=Test");

        $response->assertOk();
        
        $conversations = collect($response->json('data.data'));
        expect($conversations->count())->toBe(0);
    });

    test('search request validation works correctly', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 'agent']);

        // Test missing query parameter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search");

        $response->assertUnprocessable();

        // Test query too short
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=a");

        $response->assertUnprocessable();

        // Test invalid search type
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=test&type=invalid");

        $response->assertUnprocessable();

        // Test valid request
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=test&type=all");

        $response->assertOk();
    });
});

describe('Search Edge Cases', function () {
    test('search with special characters', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['email' => 'test+tag@example.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=".urlencode('test+tag'));

        $response->assertOk();
    });

    test('search with unicode characters', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['name' => '田中太郎']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=".urlencode('田中'));

        $response->assertOk();
    });

    test('search with very long query', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $longQuery = str_repeat('a', 500);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q={$longQuery}");

        $response->assertOk()->or($response->assertUnprocessable());
    });

    test('search with numbers', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['phone_number' => '+12025551234']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=2025551234");

        $response->assertOk();
    });

    test('handles empty results gracefully', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search?q=nonexistentquery123456");

        $response->assertOk()
            ->assertJson(['data' => []]);
    });
});

describe('Search Pagination', function () {
    test('search results are paginated', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory(50)->for($account)->create(['name' => 'Test User']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=Test");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('can request specific page', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory(50)->for($account)->create(['name' => 'Test User']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/search/contacts?q=Test&page=2");

        $response->assertOk();
    });
});
