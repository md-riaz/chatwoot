<?php

/**
 * Comprehensive Agent Bot API Tests
 *
 * Tests all agent bot-related API functionality including CRUD operations,
 * inbox associations, and edge cases.
 */

use App\Models\Account;
use App\Models\AgentBot;
use App\Models\Inbox;
use App\Models\User;

describe('Agent Bot Listing', function () {
    test('can list agent bots for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AgentBot::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_bots");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('includes global bots in listing', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AgentBot::factory(2)->for($account)->create();
        AgentBot::factory(3)->systemBot()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_bots");

        $response->assertOk();
        expect(count($response->json('data')))->toBeGreaterThanOrEqual(5);
    });

    test('agent bots list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AgentBot::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_bots");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'outgoing_url',
                        'bot_type',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Agent Bot Creation', function () {
    test('can create agent bot', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'name' => 'Support Bot',
                'description' => 'Automated support assistant',
                'outgoing_url' => 'https://bot.example.com/webhook',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Support Bot')
            ->assertJsonPath('data.outgoing_url', 'https://bot.example.com/webhook');
    });

    test('agent bot creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'outgoing_url' => 'https://bot.example.com/webhook',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('agent bot creation requires outgoing_url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'name' => 'Test Bot',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outgoing_url']);
    });

    test('agent bot creation with invalid url fails', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'name' => 'Test Bot',
                'outgoing_url' => 'not-a-url',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outgoing_url']);
    });
});

describe('Agent Bot Retrieval', function () {
    test('can show agent bot', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($account)->create(['name' => 'Test Bot']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_bots/{$bot->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $bot->id)
            ->assertJsonPath('data.name', 'Test Bot');
    });

    test('cannot access agent bot from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/agent_bots/{$bot->id}");

        $response->assertNotFound();
    });

    test('can access global bot from any account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $globalBot = AgentBot::factory()->systemBot()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_bots/{$globalBot->id}");

        $response->assertOk();
    });
});

describe('Agent Bot Update', function () {
    test('can update agent bot name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agent_bots/{$bot->id}", [
                'name' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    });

    test('can update outgoing_url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($account)->create([
            'outgoing_url' => 'https://old.example.com/webhook',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/agent_bots/{$bot->id}", [
                'outgoing_url' => 'https://new.example.com/webhook',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.outgoing_url', 'https://new.example.com/webhook');
    });
});

describe('Agent Bot Deletion', function () {
    test('can delete agent bot', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agent_bots/{$bot->id}");

        $response->assertNoContent();
        expect(AgentBot::find($bot->id))->toBeNull();
    });

    test('cannot delete global bot', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $globalBot = AgentBot::factory()->systemBot()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agent_bots/{$globalBot->id}");

        $response->assertForbidden()->or($response->assertNotFound());
    });
});

describe('Agent Bot Inbox Association', function () {
    test('can associate bot with inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($account)->create();
        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots/{$bot->id}/inboxes", [
                'inbox_id' => $inbox->id,
            ]);

        $response->assertOk();
        expect($bot->inboxes()->where('inboxes.id', $inbox->id)->exists())->toBeTrue();
    });

    test('can remove bot from inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $bot = AgentBot::factory()->for($account)->create();
        $inbox = Inbox::factory()->for($account)->create();
        $bot->inboxes()->attach($inbox->id);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/agent_bots/{$bot->id}/inboxes/{$inbox->id}");

        $response->assertOk();
        expect($bot->inboxes()->where('inboxes.id', $inbox->id)->exists())->toBeFalse();
    });
});

describe('Agent Bot Authorization', function () {
    test('unauthenticated user cannot list agent bots', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/agent_bots");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create agent bot', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
            'name' => 'Test Bot',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Agent Bot Edge Cases', function () {
    test('agent bot with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'name' => 'サポートボット 🤖',
                'outgoing_url' => 'https://bot.example.com/webhook',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'サポートボット 🤖');
    });

    test('handles many agent bots', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AgentBot::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/agent_bots");

        $response->assertOk();
    });

    test('agent bot with avatar url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'name' => 'Avatar Bot',
                'outgoing_url' => 'https://bot.example.com/webhook',
                'avatar_url' => 'https://example.com/bot-avatar.png',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.avatar_url', 'https://example.com/bot-avatar.png');
    });

    test('agent bot with long description', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $longDescription = str_repeat('This is a detailed bot description. ', 20);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/agent_bots", [
                'name' => 'Detailed Bot',
                'description' => $longDescription,
                'outgoing_url' => 'https://bot.example.com/webhook',
            ]);

        $response->assertCreated();
    });
});
