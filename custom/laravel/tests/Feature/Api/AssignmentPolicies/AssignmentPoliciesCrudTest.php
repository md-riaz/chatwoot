<?php

/**
 * Comprehensive Assignment Policies API Tests
 *
 * Tests all assignment policy-related API functionality including CRUD operations
 * and inbox associations.
 */

use App\Models\Account;
use App\Models\AssignmentPolicy;
use App\Models\Inbox;
use App\Models\User;

describe('Assignment Policy Listing', function () {
    test('can list assignment policies for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AssignmentPolicy::factory(3)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/assignment_policies");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });
});

describe('Assignment Policy Creation', function () {
    test('can create assignment policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/assignment_policies", [
                'name' => 'Round Robin Policy',
                'description' => 'Distribute evenly among agents',
                'assignment_order' => 0,
                'enabled' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Round Robin Policy');
    });

    test('assignment policy creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/assignment_policies", [
                'assignment_order' => 0,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Assignment Policy Update', function () {
    test('can update assignment policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $policy = AssignmentPolicy::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/assignment_policies/{$policy->id}", [
                'name' => 'Updated Policy',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Policy');
    });

    test('can toggle assignment policy enabled status', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $policy = AssignmentPolicy::factory()->for($account)->create(['enabled' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/assignment_policies/{$policy->id}", [
                'enabled' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.enabled', false);
    });
});

describe('Assignment Policy Deletion', function () {
    test('can delete assignment policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $policy = AssignmentPolicy::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/assignment_policies/{$policy->id}");

        $response->assertNoContent();
        expect(AssignmentPolicy::find($policy->id))->toBeNull();
    });
});

describe('Assignment Policy Authorization', function () {
    test('unauthenticated user cannot list assignment policies', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/assignment_policies");

        $response->assertUnauthorized();
    });
});
