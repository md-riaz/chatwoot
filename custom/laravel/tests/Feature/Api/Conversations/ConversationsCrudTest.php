<?php

/**
 * Comprehensive Conversation API Tests
 *
 * Tests all conversation-related API functionality including CRUD operations,
 * status management, assignment, filtering, and edge cases.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('Conversation Listing', function () {
    test('can list conversations for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

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

    test('empty account returns empty conversations list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('conversations list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'priority',
                        'inbox_id',
                        'contact_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('conversations are sorted by last activity', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $old = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['last_activity_at' => now()->subDays(2)]);

        $recent = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['last_activity_at' => now()]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        expect($ids[0])->toBe($recent->id);
    });
});

describe('Conversation Creation', function () {
    test('can create conversation', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

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

    test('new conversation defaults to open status', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'inbox_id' => $inbox->id,
                'contact_id' => $contact->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', Conversation::STATUS_OPEN);
    });

    test('conversation creation requires inbox_id', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'contact_id' => $contact->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['inbox_id']);
    });

    test('conversation creation requires contact_id', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'inbox_id' => $inbox->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_id']);
    });

    test('cannot create conversation with inbox from different account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($otherAccount)->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'inbox_id' => $inbox->id,
                'contact_id' => $contact->id,
            ]);

        $response->assertUnprocessable();
    });

    test('can create conversation with assignee', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'inbox_id' => $inbox->id,
                'contact_id' => $contact->id,
                'assignee_id' => $agent->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.assignee_id', $agent->id);
    });
});

describe('Conversation Retrieval', function () {
    test('can show conversation', function () {
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
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $conversation->id);
    });

    test('cannot access conversation from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($otherAccount)->create();
        $contact = Contact::factory()->for($otherAccount)->create();
        $conversation = Conversation::factory()
            ->for($otherAccount)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/conversations/{$conversation->id}");

        $response->assertNotFound();
    });

    test('viewing non-existent conversation returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/99999");

        $response->assertNotFound();
    });
});

describe('Conversation Assignment', function () {
    test('can assign conversation to agent', function () {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($assignee->id, ['role' => 1]);

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

    test('can unassign conversation', function () {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($assignee->id, ['role' => 1]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $assignee->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/assign", [
                'assignee_id' => null,
            ]);

        $response->assertOk();
        expect(Conversation::find($conversation->id)->assignee_id)->toBeNull();
    });

    test('assignment updates conversation', function () {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($assignee->id, ['role' => 1]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => null]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/assign", [
                'assignee_id' => $assignee->id,
            ]);

        $conversation->refresh();
        expect($conversation->assignee_id)->toBe($assignee->id);
    });
});

describe('Conversation Status', function () {
    test('can resolve conversation', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

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

    test('resolved conversation updates database', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->open()
            ->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}/resolve");

        $conversation->refresh();
        expect($conversation->status)->toBe(Conversation::STATUS_RESOLVED);
    });
});

describe('Conversation Authorization', function () {
    test('unauthenticated user cannot list conversations', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/conversations");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create conversation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/conversations", [
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        // User is NOT attached to account

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations");

        $response->assertNotFound();
    });
});

describe('Conversation Filtering', function () {
    test('can filter by status', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(3)->for($account)->for($inbox)->for($contact)->open()->create();
        Conversation::factory(2)->for($account)->for($inbox)->for($contact)->resolved()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations?status=".Conversation::STATUS_OPEN);

        $response->assertOk();
        $statuses = collect($response->json('data'))->pluck('status')->unique()->values()->toArray();
        expect($statuses)->toBe([Conversation::STATUS_OPEN]);
    });

    test('can filter by assignee', function () {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($assignee->id, ['role' => 1]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(3)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $assignee->id]);

        Conversation::factory(2)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations?assignee_id={$assignee->id}");

        $response->assertOk();
        $data = $response->json('data');
        foreach ($data as $conv) {
            expect($conv['assignee_id'])->toBe($assignee->id);
        }
    });

    test('can filter by inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox1 = Inbox::factory()->for($account)->create();
        $inbox2 = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(3)->for($account)->for($inbox1)->for($contact)->create();
        Conversation::factory(2)->for($account)->for($inbox2)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations?inbox_id={$inbox1->id}");

        $response->assertOk();
        $data = $response->json('data');
        foreach ($data as $conv) {
            expect($conv['inbox_id'])->toBe($inbox1->id);
        }
    });
});

describe('Conversation Edge Cases', function () {
    test('handles large number of conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(100)->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations");

        $response->assertOk();
    });

    test('conversation with custom attributes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['custom_attributes' => ['priority' => 'vip', 'segment' => 'enterprise']]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}");

        $response->assertOk();
    });

    test('conversation display_id is unique per account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $conv1 = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();
        $conv2 = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();

        expect($conv1->display_id)->not->toBe($conv2->display_id);
    });
});
