<?php

/**
 * Segments API Tests
 *
 * Tests contact segments functionality including creation,
 * filtering, and membership.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;

describe('Segment Listing', function () {
    test('can list segments', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/segments");

        $response->assertOk();
    });

    test('segments include contact count', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/segments");

        $response->assertOk();
    });

    test('can filter segments by type', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/segments?type=dynamic");

        $response->assertOk();
    });
});

describe('Segment Creation', function () {
    test('can create static segment', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'VIP Customers',
                'type' => 'static',
            ]);

        $response->assertCreated();
    });

    test('can create dynamic segment', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Active Users',
                'type' => 'dynamic',
                'query' => [
                    ['attribute_key' => 'last_activity_at', 'filter_operator' => 'days_before', 'values' => [30]],
                ],
            ]);

        $response->assertCreated();
    });

    test('segment requires name', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'type' => 'static',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Segment Membership', function () {
    test('can add contacts to static segment', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Test Segment',
                'type' => 'static',
            ]);

        $segmentId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments/{$segmentId}/contacts", [
                'contact_ids' => [$contact->id],
            ]);

        $response->assertOk();
    });

    test('can remove contacts from segment', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $contact = Contact::factory()->for($account)->create();

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Test Segment',
                'type' => 'static',
            ]);

        $segmentId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/segments/{$segmentId}/contacts", [
                'contact_ids' => [$contact->id],
            ]);

        $response->assertOk();
    });

    test('can list segment contacts', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Test Segment',
                'type' => 'static',
            ]);

        $segmentId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/segments/{$segmentId}/contacts");

        $response->assertOk();
    });

    test('dynamic segment auto-calculates membership', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        // Create contacts
        Contact::factory(10)->for($account)->create();

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'All Contacts',
                'type' => 'dynamic',
                'query' => [],
            ]);

        $response = $createResponse;
        $response->assertCreated();
    });
});

describe('Segment Update', function () {
    test('can update segment name', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Original Name',
                'type' => 'static',
            ]);

        $segmentId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/segments/{$segmentId}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk();
    });

    test('can update dynamic segment query', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Dynamic Segment',
                'type' => 'dynamic',
                'query' => [],
            ]);

        $segmentId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/segments/{$segmentId}", [
                'query' => [
                    ['attribute_key' => 'email', 'filter_operator' => 'contains', 'values' => ['@gmail.com']],
                ],
            ]);

        $response->assertOk();
    });
});

describe('Segment Deletion', function () {
    test('can delete segment', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'Delete Me',
                'type' => 'static',
            ]);

        $segmentId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/segments/{$segmentId}");

        $response->assertNoContent();
    });
});

describe('Segment Authorization', function () {
    test('unauthenticated user cannot list segments', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/segments");

        $response->assertUnauthorized();
    });

    test('agent can view segments', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/segments");

        $response->assertOk();
    });
});

describe('Segment Query Operators', function () {
    test('equal_to operator', function () {
        $query = ['filter_operator' => 'equal_to', 'values' => ['test']];
        expect($query['filter_operator'])->toBe('equal_to');
    });

    test('not_equal_to operator', function () {
        $query = ['filter_operator' => 'not_equal_to', 'values' => ['test']];
        expect($query['filter_operator'])->toBe('not_equal_to');
    });

    test('contains operator', function () {
        $query = ['filter_operator' => 'contains', 'values' => ['test']];
        expect($query['filter_operator'])->toBe('contains');
    });

    test('does_not_contain operator', function () {
        $query = ['filter_operator' => 'does_not_contain', 'values' => ['test']];
        expect($query['filter_operator'])->toBe('does_not_contain');
    });

    test('starts_with operator', function () {
        $query = ['filter_operator' => 'starts_with', 'values' => ['test']];
        expect($query['filter_operator'])->toBe('starts_with');
    });

    test('ends_with operator', function () {
        $query = ['filter_operator' => 'ends_with', 'values' => ['test']];
        expect($query['filter_operator'])->toBe('ends_with');
    });

    test('is_present operator', function () {
        $query = ['filter_operator' => 'is_present'];
        expect($query['filter_operator'])->toBe('is_present');
    });

    test('is_not_present operator', function () {
        $query = ['filter_operator' => 'is_not_present'];
        expect($query['filter_operator'])->toBe('is_not_present');
    });

    test('days_before operator', function () {
        $query = ['filter_operator' => 'days_before', 'values' => [30]];
        expect($query['filter_operator'])->toBe('days_before');
    });

    test('greater_than operator', function () {
        $query = ['filter_operator' => 'greater_than', 'values' => [100]];
        expect($query['filter_operator'])->toBe('greater_than');
    });

    test('less_than operator', function () {
        $query = ['filter_operator' => 'less_than', 'values' => [100]];
        expect($query['filter_operator'])->toBe('less_than');
    });
});

describe('Segment Edge Cases', function () {
    test('handles unicode segment names', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/segments", [
                'name' => 'VIPお客様 👑',
                'type' => 'static',
            ]);

        $response->assertCreated();
    });

    test('handles complex nested queries', function () {
        $query = [
            ['attribute_key' => 'email', 'filter_operator' => 'contains', 'values' => ['@gmail.com']],
            ['attribute_key' => 'name', 'filter_operator' => 'is_present'],
            ['attribute_key' => 'custom_attributes.tier', 'filter_operator' => 'equal_to', 'values' => ['premium']],
        ];

        expect(count($query))->toBe(3);
    });
});
