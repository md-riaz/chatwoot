<?php

use App\Enums\AccountStatus;
use App\Models\Account;
use App\Models\User;

test('can list accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/accounts');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'locale', 'status'],
            ],
        ]);
});

test('can create account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/accounts', [
            'name' => 'Test Account',
            'locale' => 'en',
            'status' => 'active',  // Use string value like Rails
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Test Account');
});

test('can show account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/accounts/{$account->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $account->id);
});

test('can update account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $response = $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/accounts/{$account->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');
});

test('can delete account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $account->users()->attach($user->id, ['role' =>   0]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/accounts/{$account->id}");

    $response->assertNoContent();
    expect(Account::find($account->id))->toBeNull();
});

test('unauthenticated user cannot access accounts', function () {
    $response = $this->getJson('/api/v1/accounts');

    $response->assertUnauthorized();
});
