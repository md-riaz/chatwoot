<?php

/**
 * Comprehensive Custom Filter API Tests
 *
 * Tests all custom filter-related API functionality including CRUD operations
 * and edge cases.
 */

use App\Models\Account;
use App\Models\CustomFilter;
use App\Models\User;

describe('Custom Filter Listing', function () {
    test('can list custom filters for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CustomFilter::factory(5)->for($account)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('user sees only their own filters', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($otherUser->id, ['role' => 1]);

        CustomFilter::factory(3)->for($account)->for($user)->create();
        CustomFilter::factory(2)->for($account)->for($otherUser)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('custom filters list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CustomFilter::factory()->for($account)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'filter_type',
                        'query',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('can filter by type', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CustomFilter::factory(3)->for($account)->for($user)->conversation()->create();
        CustomFilter::factory(2)->for($account)->for($user)->contact()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters?filter_type=".CustomFilter::TYPE_CONVERSATION);

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('filter_type')->unique()->values()->toArray();
        expect($types)->toBe([CustomFilter::TYPE_CONVERSATION]);
    });
});

describe('Custom Filter Creation', function () {
    test('can create conversation filter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'name' => 'Open Conversations',
                'filter_type' => CustomFilter::TYPE_CONVERSATION,
                'query' => [
                    [
                        'attribute_key' => 'status',
                        'filter_operator' => 'equal_to',
                        'values' => ['open'],
                        'query_operator' => 'and',
                    ],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Open Conversations')
            ->assertJsonPath('data.filter_type', CustomFilter::TYPE_CONVERSATION);
    });

    test('can create contact filter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'name' => 'Enterprise Contacts',
                'filter_type' => CustomFilter::TYPE_CONTACT,
                'query' => [
                    [
                        'attribute_key' => 'custom_attribute.tier',
                        'filter_operator' => 'equal_to',
                        'values' => ['enterprise'],
                        'query_operator' => 'and',
                    ],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.filter_type', CustomFilter::TYPE_CONTACT);
    });

    test('custom filter creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'filter_type' => CustomFilter::TYPE_CONVERSATION,
                'query' => [],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('custom filter creation requires filter_type', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'name' => 'Test Filter',
                'query' => [],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['filter_type']);
    });

    test('custom filter creation requires query', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'name' => 'Test Filter',
                'filter_type' => CustomFilter::TYPE_CONVERSATION,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['query']);
    });
});

describe('Custom Filter Retrieval', function () {
    test('can show custom filter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $filter = CustomFilter::factory()->for($account)->for($user)->create(['name' => 'My Filter']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters/{$filter->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $filter->id)
            ->assertJsonPath('data.name', 'My Filter');
    });

    test('cannot access other users filter', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($otherUser->id, ['role' => 1]);

        $filter = CustomFilter::factory()->for($account)->for($otherUser)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters/{$filter->id}");

        $response->assertNotFound();
    });
});

describe('Custom Filter Update', function () {
    test('can update custom filter name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $filter = CustomFilter::factory()->for($account)->for($user)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/custom_filters/{$filter->id}", [
                'name' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    });

    test('can update query', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $filter = CustomFilter::factory()->for($account)->for($user)->create();

        $newQuery = [
            [
                'attribute_key' => 'priority',
                'filter_operator' => 'equal_to',
                'values' => ['high'],
                'query_operator' => 'and',
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/custom_filters/{$filter->id}", [
                'query' => $newQuery,
            ]);

        $response->assertOk();
        $filter->refresh();
        expect($filter->query[0]['attribute_key'])->toBe('priority');
    });
});

describe('Custom Filter Deletion', function () {
    test('can delete custom filter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $filter = CustomFilter::factory()->for($account)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/custom_filters/{$filter->id}");

        $response->assertNoContent();
        expect(CustomFilter::find($filter->id))->toBeNull();
    });

    test('cannot delete other users filter', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($otherUser->id, ['role' => 1]);

        $filter = CustomFilter::factory()->for($account)->for($otherUser)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/custom_filters/{$filter->id}");

        $response->assertNotFound();
    });
});

describe('Custom Filter Authorization', function () {
    test('unauthenticated user cannot list custom filters', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/custom_filters");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create custom filter', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
            'name' => 'Test Filter',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Custom Filter Edge Cases', function () {
    test('custom filter with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'name' => 'フィルター 📋',
                'filter_type' => CustomFilter::TYPE_CONVERSATION,
                'query' => [
                    ['attribute_key' => 'status', 'filter_operator' => 'equal_to', 'values' => ['open'], 'query_operator' => 'and'],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'フィルター 📋');
    });

    test('complex filter with multiple conditions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_filters", [
                'name' => 'Complex Filter',
                'filter_type' => CustomFilter::TYPE_CONVERSATION,
                'query' => [
                    ['attribute_key' => 'status', 'filter_operator' => 'equal_to', 'values' => ['open'], 'query_operator' => 'and'],
                    ['attribute_key' => 'inbox_id', 'filter_operator' => 'equal_to', 'values' => [1, 2, 3], 'query_operator' => 'and'],
                    ['attribute_key' => 'assignee_id', 'filter_operator' => 'is_not_present', 'values' => [], 'query_operator' => 'and'],
                ],
            ]);

        $response->assertCreated();
    });

    test('handles many custom filters', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CustomFilter::factory(50)->for($account)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_filters");

        $response->assertOk();
    });
});
