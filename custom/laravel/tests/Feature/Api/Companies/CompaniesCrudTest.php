<?php

/**
 * Comprehensive Companies API Tests
 *
 * Tests all company-related API functionality including CRUD operations,
 * search, and edge cases.
 */

use App\Models\Account;
use App\Models\Company;
use App\Models\User;

describe('Company Listing', function () {
    test('can list companies for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Company::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/companies");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty companies list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/companies");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });
});

describe('Company Creation', function () {
    test('can create company', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/companies", [
                'name' => 'Acme Corporation',
                'domain' => 'acme.com',
                'description' => 'Test company',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Acme Corporation')
            ->assertJsonPath('data.domain', 'acme.com');
    });

    test('company creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/companies", [
                'domain' => 'test.com',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Company Retrieval', function () {
    test('can show company', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $company = Company::factory()->for($account)->create(['name' => 'Test Company']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/companies/{$company->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Test Company');
    });

    test('cannot access company from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $company = Company::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/companies/{$company->id}");

        $response->assertNotFound();
    });
});

describe('Company Update', function () {
    test('can update company', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $company = Company::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/companies/{$company->id}", [
                'name' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    });
});

describe('Company Deletion', function () {
    test('can delete company', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $company = Company::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/companies/{$company->id}");

        $response->assertNoContent();
        expect(Company::find($company->id))->toBeNull();
    });
});

describe('Company Search', function () {
    test('can search companies by name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Company::factory()->for($account)->create(['name' => 'Acme Corp']);
        Company::factory()->for($account)->create(['name' => 'Beta Inc']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/companies/search?q=Acme");

        $response->assertOk();
    });
});

describe('Company Authorization', function () {
    test('unauthenticated user cannot list companies', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/companies");

        $response->assertUnauthorized();
    });
});
