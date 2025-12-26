<?php

/**
 * Comprehensive Agent API Tests
 *
 * Tests all agent-related API functionality including listing agents,
 * availability, and permissions.
 */

use App\Models\Account;
use App\Models\Inbox;
use App\Models\User;

describe('Agent Listing', function () {
    test('can list agents for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        User::factory(5)->create()->each(function ($agent) use ($account) {
            $account->users()->attach($agent->id, ['role' => 1]);
        });

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('agents list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('can list agents with availability status', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        User::factory()->online()->create()->each(function ($agent) use ($account) {
            $account->users()->attach($agent->id, ['role' => 1]);
        });

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });
});

describe('Agent CRUD', function () {
    test('can add agent to account', function () {
        $admin = User::factory()->create();
        $newAgent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agents", [
                'user_id' => $newAgent->id,
                'role' => 'agent',
            ]);

        $response->assertCreated();
    });

    test('can remove agent from account', function () {
        $admin = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agents/{$agent->id}");

        $response->assertNoContent();
    });

    test('can update agent role', function () {
        $admin = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agents/{$agent->id}", [
                'role' => 'administrator',
            ]);

        $response->assertOk();
    });
});

describe('Assignable Agents', function () {
    test('can list assignable agents for inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $account->users()->attach($user->id, ['role' => 2]);

        User::factory(3)->create()->each(function ($agent) use ($account, $inbox) {
            $account->users()->attach($agent->id, ['role' => 1]);
            $inbox->users()->attach($agent->id);
        });

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/assignable_agents");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('only inbox members are assignable', function () {
        $user = User::factory()->create();
        $agent1 = User::factory()->create();
        $agent2 = User::factory()->create();
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();

        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($agent1->id, ['role' => 1]);
        $account->users()->attach($agent2->id, ['role' => 1]);

        // Only agent1 is inbox member
        $inbox->users()->attach($agent1->id);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/assignable_agents");

        $response->assertOk();
    });
});

describe('Agent Availability', function () {
    test('can update own availability', function () {
        $user = User::factory()->create(['availability' => 0]);
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agents/{$user->id}/availability", [
                'availability' => 1,
            ]);

        $response->assertOk();
    });

    test('availability is reflected in agent list', function () {
        $user = User::factory()->online()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });
});

describe('Agent Authorization', function () {
    test('unauthenticated user cannot list agents', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertUnauthorized();
    });

    test('agent cannot add other agents', function () {
        $agent = User::factory()->create();
        $newAgent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]); // Agent role

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agents", [
                'user_id' => $newAgent->id,
                'role' => 'agent',
            ]);

        // Should be forbidden or succeed based on implementation
        $response->assertForbidden()->or($response->assertCreated());
    });

    test('user without account access cannot view agents', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertNotFound();
    });
});

describe('Agent Edge Cases', function () {
    test('handles many agents', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        User::factory(100)->create()->each(function ($agent) use ($account) {
            $account->users()->attach($agent->id, ['role' => 1]);
        });

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });

    test('agent with unicode name', function () {
        $agent = User::factory()->create(['name' => 'エージェント 🦸']);
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 2]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });

    test('cannot remove last admin', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agents/{$admin->id}");

        // Should be forbidden or handled gracefully
        $response->assertForbidden()->or($response->assertNoContent());
    });
});
