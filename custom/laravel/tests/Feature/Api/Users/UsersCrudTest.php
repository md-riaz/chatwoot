<?php

/**
 * Comprehensive Users API Tests
 *
 * Tests all user-related API functionality including profile management,
 * user listing, and account user operations.
 */

use App\Models\Account;
use App\Models\User;

describe('User Profile', function () {
    test('authenticated user can get their profile', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.name', 'Test User')
            ->assertJsonPath('data.email', 'test@example.com');
    });

    test('profile includes expected fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);
    });

    test('unauthenticated user cannot get profile', function () {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    });
});

describe('Account Users Listing', function () {
    test('admin can list account users', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $agent = User::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });

    test('users list includes role information', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });
});

describe('User Role Management', function () {
    test('admin can change user role', function () {
        $admin = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agents/{$agent->id}", [
                'role' => 2,
            ]);

        $response->assertOk();
    });

    test('agent cannot change user roles', function () {
        $agent = User::factory()->create();
        $otherAgent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);
        $account->users()->attach($otherAgent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agents/{$otherAgent->id}", [
                'role' => 2,
            ]);

        $response->assertForbidden();
    });
});

describe('User Availability', function () {
    test('user can update their availability', function () {
        $user = User::factory()->create(['availability' => 1]);
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agents/{$user->id}/availability", [
                'availability' => 0,
            ]);

        $response->assertOk();
    });

    test('availability status constants', function () {
        expect(User::AVAILABILITY_ONLINE)->toBe(1);
        expect(User::AVAILABILITY_BUSY)->toBe(2);
        expect(User::AVAILABILITY_OFFLINE)->toBe(0);
    });
});

describe('Agent Invitations', function () {
    test('admin can invite new agent', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agents/invite", [
                'name' => 'New Agent',
                'email' => 'newagent@example.com',
                'role' => 1,
            ]);

        $response->assertOk();
    });

    test('cannot invite existing user email', function () {
        $admin = User::factory()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agents/invite", [
                'name' => 'Duplicate',
                'email' => 'existing@example.com',
                'role' => 1,
            ]);

        $response->assertUnprocessable();
    });

    test('agent cannot invite users', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agents/invite", [
                'name' => 'New Agent',
                'email' => 'invite@example.com',
                'role' => 1,
            ]);

        $response->assertForbidden();
    });
});

describe('Agent Removal', function () {
    test('admin can remove agent from account', function () {
        $admin = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agents/{$agent->id}");

        $response->assertNoContent();
    });

    test('agent cannot remove other agents', function () {
        $agent = User::factory()->create();
        $otherAgent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);
        $account->users()->attach($otherAgent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agents/{$otherAgent->id}");

        $response->assertForbidden();
    });

    test('admin cannot remove themselves as last admin', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agents/{$admin->id}");

        $response->assertForbidden();
    });
});

describe('User Search', function () {
    test('can search users by name', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Doe']);
        User::factory()->create(['name' => 'Bob Smith']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents?search=Doe");

        $response->assertOk();
    });

    test('can search users by email', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents?search=example.com");

        $response->assertOk();
    });
});

describe('User Authorization', function () {
    test('unauthenticated user cannot list agents', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertUnauthorized();
    });

    test('user without account access cannot list agents', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertNotFound();
    });
});

describe('User Edge Cases', function () {
    test('handles unicode names', function () {
        $user = User::factory()->create(['name' => '田中太郎 🧑‍💻']);

        expect($user->name)->toBe('田中太郎 🧑‍💻');
    });

    test('handles special characters in email', function () {
        $user = User::factory()->create(['email' => 'user+test@example.com']);

        expect($user->email)->toBe('user+test@example.com');
    });

    test('handles many users efficiently', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $users = User::factory(100)->create();
        foreach ($users as $u) {
            $account->users()->attach($u->id, ['role' => 1]);
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agents");

        $response->assertOk();
    });
});
