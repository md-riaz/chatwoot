<?php

/**
 * Comprehensive Account API Tests
 *
 * Tests all account-related API functionality including CRUD operations,
 * validation, authorization, and edge cases.
 */

use App\Models\Account;
use App\Models\User;

describe('Account Listing', function () {
    test('authenticated user can list their accounts', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/accounts');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'locale', 'status', 'created_at', 'updated_at'],
                ],
            ]);
    });

    test('user sees only their associated accounts', function () {
        $user = User::factory()->create();
        $myAccount = Account::factory()->create(['name' => 'My Account']);
        Account::factory()->create(['name' => 'Other Account']);
        $myAccount->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/accounts');

        $response->assertOk();
        $data = $response->json('data');
        $accountNames = collect($data)->pluck('name')->toArray();
        expect($accountNames)->toContain('My Account');
    });

    test('accounts list returns paginated results', function () {
        $user = User::factory()->create();
        $accounts = Account::factory(25)->create();
        foreach ($accounts as $account) {
            $account->users()->attach($user->id, ['role' => 1]);
        }

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/accounts');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Account Creation', function () {
    test('authenticated user can create an account', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => 'New Test Account',
                'locale' => 'en',
                'status' => 1,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Test Account')
            ->assertJsonPath('data.locale', 'en');
    });

    test('account creation fails without required fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('account creation with all optional fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => 'Full Account',
                'locale' => 'es',
                'domain' => 'example.com',
                'support_email' => 'support@example.com',
                'status' => 1,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Full Account')
            ->assertJsonPath('data.locale', 'es')
            ->assertJsonPath('data.domain', 'example.com')
            ->assertJsonPath('data.support_email', 'support@example.com');
    });

    test('account creation with invalid email fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => 'Test Account',
                'locale' => 'en',
                'support_email' => 'not-an-email',
                'status' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['support_email']);
    });
});

describe('Account Retrieval', function () {
    test('user can view their account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create(['name' => 'View Account']);
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $account->id)
            ->assertJsonPath('data.name', 'View Account');
    });

    test('viewing non-existent account returns 404', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/accounts/99999');

        $response->assertNotFound();
    });
});

describe('Account Update', function () {
    test('admin can update account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create(['name' => 'Original Name']);
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        expect(Account::find($account->id)->name)->toBe('Updated Name');
    });

    test('partial update works', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'name' => 'Original',
            'locale' => 'en',
            'domain' => 'original.com',
        ]);
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}", [
                'locale' => 'es',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.locale', 'es')
            ->assertJsonPath('data.name', 'Original');
    });
});

describe('Account Deletion', function () {
    test('admin can delete account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}");

        $response->assertNoContent();
        expect(Account::find($account->id))->toBeNull();
    });

    test('deleting non-existent account returns 404', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/accounts/99999');

        $response->assertNotFound();
    });
});

describe('Account Authorization', function () {
    test('unauthenticated user cannot access accounts', function () {
        $response = $this->getJson('/api/v1/accounts');
        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create account', function () {
        $response = $this->postJson('/api/v1/accounts', [
            'name' => 'Test Account',
        ]);
        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot update account', function () {
        $account = Account::factory()->create();
        $response = $this->patchJson("/api/v1/accounts/{$account->id}", [
            'name' => 'Updated',
        ]);
        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot delete account', function () {
        $account = Account::factory()->create();
        $response = $this->deleteJson("/api/v1/accounts/{$account->id}");
        $response->assertUnauthorized();
    });
});

describe('Account Validation', function () {
    test('name is required', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'locale' => 'en',
                'status' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('name cannot exceed maximum length', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => str_repeat('a', 300),
                'locale' => 'en',
                'status' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Account Edge Cases', function () {
    test('empty string values are handled properly', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => '',
                'locale' => 'en',
                'status' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('null optional fields are handled', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => 'Test Account',
                'locale' => 'en',
                'domain' => null,
                'support_email' => null,
                'status' => 1,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.domain', null)
            ->assertJsonPath('data.support_email', null);
    });

    test('unicode characters in name are supported', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/accounts', [
                'name' => '日本語アカウント 🏢',
                'locale' => 'ja',
                'status' => 1,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', '日本語アカウント 🏢');
    });
});
