<?php

/**
 * Comprehensive Dashboard Apps API Tests
 *
 * Tests all dashboard apps functionality including CRUD operations,
 * configuration, and authorization.
 */

use App\Models\Account;
use App\Models\User;

describe('Dashboard Apps Listing', function () {
    test('can list dashboard apps', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertOk();
    });

    test('returns empty list for account without apps', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('list includes expected fields', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Dashboard Apps Creation', function () {
    test('can create dashboard app', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Customer Insights',
                'content' => [
                    'type' => 'frame',
                    'url' => 'https://example.com/insights',
                ],
            ]);

        $response->assertCreated();
    });

    test('can create frame type app', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'External Dashboard',
                'content' => [
                    'type' => 'frame',
                    'url' => 'https://dashboard.example.com',
                ],
            ]);

        $response->assertCreated();
    });

    test('requires title', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'content' => [
                    'type' => 'frame',
                    'url' => 'https://example.com',
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    test('requires content', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Test App',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });
});

describe('Dashboard Apps Retrieval', function () {
    test('can show dashboard app', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Test App',
                'content' => ['type' => 'frame', 'url' => 'https://example.com'],
            ]);

        $appId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps/{$appId}");

        $response->assertOk();
    });

    test('returns 404 for non-existent app', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps/99999");

        $response->assertNotFound();
    });
});

describe('Dashboard Apps Update', function () {
    test('can update dashboard app title', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Original Title',
                'content' => ['type' => 'frame', 'url' => 'https://example.com'],
            ]);

        $appId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/dashboard_apps/{$appId}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk();
    });

    test('can update dashboard app url', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Test App',
                'content' => ['type' => 'frame', 'url' => 'https://old.example.com'],
            ]);

        $appId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/dashboard_apps/{$appId}", [
                'content' => ['type' => 'frame', 'url' => 'https://new.example.com'],
            ]);

        $response->assertOk();
    });
});

describe('Dashboard Apps Deletion', function () {
    test('can delete dashboard app', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Delete Me',
                'content' => ['type' => 'frame', 'url' => 'https://example.com'],
            ]);

        $appId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/dashboard_apps/{$appId}");

        $response->assertNoContent();
    });

    test('deleting non-existent app returns 404', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/dashboard_apps/99999");

        $response->assertNotFound();
    });
});

describe('Dashboard Apps Authorization', function () {
    test('unauthenticated user cannot list apps', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertUnauthorized();
    });

    test('agent cannot create apps', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Test',
                'content' => ['type' => 'frame', 'url' => 'https://example.com'],
            ]);

        $response->assertForbidden();
    });

    test('agent can view apps', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertOk();
    });

    test('user without account access cannot view apps', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertNotFound();
    });
});

describe('Dashboard Apps Edge Cases', function () {
    test('handles unicode titles', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => '顧客情報 📊',
                'content' => ['type' => 'frame', 'url' => 'https://example.com'],
            ]);

        $response->assertCreated();
    });

    test('handles many apps', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($admin, 'sanctum')
                ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                    'title' => "App {$i}",
                    'content' => ['type' => 'frame', 'url' => "https://example{$i}.com"],
                ]);
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/dashboard_apps");

        $response->assertOk();
    });

    test('validates URL format', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/dashboard_apps", [
                'title' => 'Invalid URL',
                'content' => ['type' => 'frame', 'url' => 'not-a-valid-url'],
            ]);

        $response->assertUnprocessable();
    });
});
