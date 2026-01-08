<?php

/**
 * Comprehensive Webhook API Tests
 *
 * Tests all webhook-related API functionality including CRUD operations,
 * subscriptions, and edge cases.
 */

use App\Models\Account;
use App\Models\Inbox;
use App\Models\User;
use App\Models\Webhook;

describe('Webhook Listing', function () {
    test('can list webhooks for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Webhook::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty webhooks list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('webhooks list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Webhook::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'url',
                        'subscriptions',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Webhook Creation', function () {
    test('can create webhook', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook',
                'subscriptions' => ['conversation_created', 'message_created'],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.url', 'https://example.com/webhook');
    });

    test('webhook creation requires url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'subscriptions' => ['conversation_created'],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    });

    test('webhook creation with invalid url fails', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'not-a-valid-url',
                'subscriptions' => ['conversation_created'],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    });

    test('webhook creation with all subscriptions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $allSubscriptions = [
            'conversation_created',
            'conversation_status_changed',
            'conversation_updated',
            'message_created',
            'message_updated',
            'webwidget_triggered',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook',
                'subscriptions' => $allSubscriptions,
            ]);

        $response->assertCreated();
        $webhook = Webhook::latest()->first();
        expect(count($webhook->subscriptions))->toBe(6);
    });

    test('webhook can be associated with inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook',
                'inbox_id' => $inbox->id,
                'subscriptions' => ['conversation_created'],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.inbox_id', $inbox->id);
    });
});

describe('Webhook Retrieval', function () {
    test('can show webhook', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $webhook = Webhook::factory()->for($account)->create([
            'url' => 'https://example.com/test',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks/{$webhook->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $webhook->id)
            ->assertJsonPath('data.url', 'https://example.com/test');
    });

    test('cannot access webhook from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $webhook = Webhook::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/webhooks/{$webhook->id}");

        $response->assertNotFound();
    });
});

describe('Webhook Update', function () {
    test('can update webhook url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $webhook = Webhook::factory()->for($account)->create([
            'url' => 'https://old.example.com/webhook',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/webhooks/{$webhook->id}", [
                'url' => 'https://new.example.com/webhook',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.url', 'https://new.example.com/webhook');
    });

    test('can update subscriptions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $webhook = Webhook::factory()->for($account)->create([
            'subscriptions' => ['conversation_created'],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/webhooks/{$webhook->id}", [
                'subscriptions' => ['message_created', 'message_updated'],
            ]);

        $response->assertOk();
        $webhook->refresh();
        expect($webhook->subscriptions)->toContain('message_created');
    });
});

describe('Webhook Deletion', function () {
    test('can delete webhook', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $webhook = Webhook::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/webhooks/{$webhook->id}");

        $response->assertNoContent();
        expect(Webhook::find($webhook->id))->toBeNull();
    });

    test('deleting non-existent webhook returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/webhooks/99999");

        $response->assertNotFound();
    });
});

describe('Webhook Authorization', function () {
    test('unauthenticated user cannot list webhooks', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/webhooks");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create webhook', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/webhooks", [
            'url' => 'https://example.com/webhook',
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view webhooks', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks");

        $response->assertNotFound();
    });
});

describe('Webhook Validation', function () {
    test('url is required', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    });

    test('url must be valid url format', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'invalid-url',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    });

    test('subscriptions must be array', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook',
                'subscriptions' => 'not-an-array',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['subscriptions']);
    });
});

describe('Webhook Edge Cases', function () {
    test('handles many webhooks', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Webhook::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks");

        $response->assertOk();
    });

    test('webhook with https url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://secure.example.com/webhook',
                'subscriptions' => ['conversation_created'],
            ]);

        $response->assertCreated();
    });

    test('webhook with port in url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com:8080/webhook',
                'subscriptions' => ['conversation_created'],
            ]);

        $response->assertCreated();
    });

    test('webhook with query parameters in url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook?token=secret123',
                'subscriptions' => ['conversation_created'],
            ]);

        $response->assertCreated();
    });
});
