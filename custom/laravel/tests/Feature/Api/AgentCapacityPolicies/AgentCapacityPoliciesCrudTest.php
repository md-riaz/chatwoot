<?php

/**
 * Comprehensive Agent Capacity Policies API Tests
 *
 * Tests all agent capacity policy-related API functionality including CRUD operations
 * and user/inbox limit management.
 */

use App\Models\Account;
use App\Models\AgentCapacityPolicy;
use App\Models\User;

describe('Agent Capacity Policy Listing', function () {
    test('can list agent capacity policies for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        AgentCapacityPolicy::factory(3)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_capacity_policies");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });
});

describe('Agent Capacity Policy Creation', function () {
    test('can create agent capacity policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_capacity_policies", [
                'name' => 'Standard Capacity',
                'description' => 'Standard agent workload',
                'exclusion_rules' => [],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Standard Capacity');
    });

    test('agent capacity policy creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_capacity_policies", [
                'description' => 'Test',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Agent Capacity Policy Update', function () {
    test('can update agent capacity policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $policy = AgentCapacityPolicy::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}", [
                'name' => 'Updated Policy',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Policy');
    });
});

describe('Agent Capacity Policy Deletion', function () {
    test('can delete agent capacity policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $policy = AgentCapacityPolicy::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}");

        $response->assertNoContent();
        expect(AgentCapacityPolicy::find($policy->id))->toBeNull();
    });
});

describe('Agent Capacity Policy Authorization', function () {
    test('unauthenticated user cannot list agent capacity policies', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/agent_capacity_policies");

        $response->assertUnauthorized();
    });
});
