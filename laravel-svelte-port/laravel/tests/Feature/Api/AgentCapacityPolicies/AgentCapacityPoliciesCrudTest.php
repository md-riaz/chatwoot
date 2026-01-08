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

describe('Agent Capacity Policy Validation', function () {
    test('validates exclusion rules on creation', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_capacity_policies", [
                'name' => 'Test Policy',
                'exclusion_rules' => [
                    'overall_capacity' => -5, // Invalid negative value
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.overall_capacity', 'Overall capacity must be a positive integer');
    });

    test('validates exclusion rules on update', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $policy = AgentCapacityPolicy::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}", [
                'exclusion_rules' => [
                    'exclude_older_than_hours' => 'invalid', // Invalid non-integer value
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.exclude_older_than_hours', 'Exclude older than hours must be a positive integer');
    });
});

describe('Agent Capacity Policy Inbox Limits', function () {
    test('can add inbox capacity limit', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $inbox = \App\Models\Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}/inbox_limits", [
                'inbox_id' => $inbox->id,
                'conversation_limit' => 5,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.conversation_limit', 5);
    });

    test('inbox capacity limit requires positive conversation limit', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $inbox = \App\Models\Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}/inbox_limits", [
                'inbox_id' => $inbox->id,
                'conversation_limit' => 0, // Invalid zero value
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['conversation_limit']);
    });
});

describe('Agent Capacity Policy User Management', function () {
    test('can assign user to capacity policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $agent = User::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]); // Agent role

        $policy = AgentCapacityPolicy::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}/users", [
                'user_id' => $agent->id,
            ]);

        $response->assertCreated();

        // Verify the assignment
        $accountUser = $account->accountUsers()->where('user_id', $agent->id)->first();
        expect($accountUser->agent_capacity_policy_id)->toBe($policy->id);
    });

    test('can remove user from capacity policy', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $agent = User::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1, 'agent_capacity_policy_id' => null]);

        $policy = AgentCapacityPolicy::factory()->for($account)->create();

        // First assign the user
        $account->accountUsers()
            ->where('user_id', $agent->id)
            ->update(['agent_capacity_policy_id' => $policy->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agent_capacity_policies/{$policy->id}/users", [
                'user_id' => $agent->id,
            ]);

        $response->assertNoContent();

        // Verify the removal
        $accountUser = $account->accountUsers()->where('user_id', $agent->id)->first();
        expect($accountUser->agent_capacity_policy_id)->toBeNull();
    });
});
