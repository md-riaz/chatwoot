<?php

/**
 * Comprehensive Custom Roles API Tests
 *
 * Tests all custom role-related API functionality including CRUD operations.
 */

use App\Models\Account;
use App\Models\CustomRole;
use App\Models\User;

describe('Custom Role Listing', function () {
    test('can list custom roles for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CustomRole::factory(3)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_roles");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });
});

describe('Custom Role Creation', function () {
    test('can create custom role', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_roles", [
                'name' => 'Support Lead',
                'description' => 'Lead support agent with extra permissions',
                'permissions' => ['conversation.assign', 'conversation.resolve'],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Support Lead');
    });

    test('custom role creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_roles", [
                'permissions' => ['conversation.view'],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Custom Role Update', function () {
    test('can update custom role', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $role = CustomRole::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/custom_roles/{$role->id}", [
                'name' => 'Updated Role',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Role');
    });
});

describe('Custom Role Deletion', function () {
    test('can delete custom role', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $role = CustomRole::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/custom_roles/{$role->id}");

        $response->assertNoContent();
        expect(CustomRole::find($role->id))->toBeNull();
    });
});

describe('Custom Role Authorization', function () {
    test('unauthenticated user cannot list custom roles', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/custom_roles");

        $response->assertUnauthorized();
    });
});
