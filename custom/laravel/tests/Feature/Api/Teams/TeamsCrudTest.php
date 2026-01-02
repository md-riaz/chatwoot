<?php

/**
 * Comprehensive Team API Tests
 *
 * Tests all team-related API functionality including CRUD operations,
 * members management, and edge cases.
 */

use App\Models\Account;
use App\Models\Team;
use App\Models\User;

describe('Team Listing', function () {
    test('can list teams for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Team::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty teams list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('teams list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Team::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'allow_auto_assign',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Team Creation', function () {
    test('can create team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/teams", [
                'name' => 'Support Team',
                'description' => 'Handles customer support',
                'allow_auto_assign' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Support Team')
            ->assertJsonPath('data.description', 'Handles customer support')
            ->assertJsonPath('data.allow_auto_assign', true);
    });

    test('team creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/teams", [
                'description' => 'Some description',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('team creation with minimal data', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/teams", [
                'name' => 'Minimal Team',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Minimal Team');
    });
});

describe('Team Retrieval', function () {
    test('can show team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $team = Team::factory()->for($account)->create(['name' => 'Test Team']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams/{$team->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $team->id)
            ->assertJsonPath('data.name', 'Test Team');
    });

    test('cannot access team from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $team = Team::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/teams/{$team->id}");

        $response->assertNotFound();
    });
});

describe('Team Update', function () {
    test('can update team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $team = Team::factory()->for($account)->create(['name' => 'Original Name']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/teams/{$team->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    });

    test('can toggle auto assignment', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $team = Team::factory()->for($account)->create(['allow_auto_assign' => false]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/teams/{$team->id}", [
                'allow_auto_assign' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.allow_auto_assign', true);
    });
});

describe('Team Deletion', function () {
    test('can delete team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $team = Team::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/teams/{$team->id}");

        $response->assertNoContent();
        expect(Team::find($team->id))->toBeNull();
    });

    test('deleting non-existent team returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/teams/99999");

        $response->assertNotFound();
    });
});

describe('Team Members', function () {
    test('can list team members', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $team = Team::factory()->for($account)->create();
        $team->members()->attach($agent->id);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams/{$team->id}/members");

        $response->assertOk();
    });

    test('can add member to team', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $team = Team::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/teams/{$team->id}/members", [
                'user_ids' => [$agent->id],
            ]);

        $response->assertOk();
        expect($team->members()->where('users.id', $agent->id)->exists())->toBeTrue();
    });

    test('can remove member from team', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $team = Team::factory()->for($account)->create();
        $team->members()->attach($agent->id);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/teams/{$team->id}/members", [
                'user_ids' => [$agent->id],
            ]);

        $response->assertOk();
        expect($team->members()->where('users.id', $agent->id)->exists())->toBeFalse();
    });
});

describe('Team Authorization', function () {
    test('unauthenticated user cannot list teams', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/teams");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create team', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/teams", [
            'name' => 'Test Team',
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view teams', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams");

        $response->assertNotFound();
    });
});

describe('Team Edge Cases', function () {
    test('team with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/teams", [
                'name' => '技術サポート 🛠️',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', '技術サポート 🛠️');
    });

    test('handles many teams', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Team::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/teams");

        $response->assertOk();
    });

    test('team with long description', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $longDescription = str_repeat('This is a detailed description. ', 50);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/teams", [
                'name' => 'Detailed Team',
                'description' => $longDescription,
            ]);

        $response->assertCreated();
    });
});
