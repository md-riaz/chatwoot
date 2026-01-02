<?php

/**
 * Comprehensive Inbox API Tests
 *
 * Tests all inbox-related API functionality including CRUD operations,
 * channel configuration, members, and edge cases.
 */

use App\Models\Account;
use App\Models\Inbox;
use App\Models\User;

describe('Inbox Listing', function () {
    test('can list inboxes for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Inbox::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty inboxes list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('inboxes list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'channel_type',
                        'enable_auto_assignment',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Inbox Creation', function () {
    test('can create inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Support Inbox',
                'channel_type' => 'Channel::WebWidget',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Support Inbox');
    });

    test('inbox creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'channel_type' => 'Channel::WebWidget',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('inbox creation with auto assignment enabled', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Auto Assign Inbox',
                'channel_type' => 'Channel::WebWidget',
                'enable_auto_assignment' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.enable_auto_assignment', true);
    });

    test('inbox creation with greeting message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Greeting Inbox',
                'channel_type' => 'Channel::WebWidget',
                'greeting_enabled' => true,
                'greeting_message' => 'Hello! How can we help you today?',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.greeting_enabled', true)
            ->assertJsonPath('data.greeting_message', 'Hello! How can we help you today?');
    });
});

describe('Inbox Retrieval', function () {
    test('can show inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create(['name' => 'Test Inbox']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $inbox->id)
            ->assertJsonPath('data.name', 'Test Inbox');
    });

    test('cannot access inbox from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/inboxes/{$inbox->id}");

        $response->assertNotFound();
    });

    test('viewing non-existent inbox returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/99999");

        $response->assertNotFound();
    });
});

describe('Inbox Update', function () {
    test('can update inbox name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create(['name' => 'Original Name']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    });

    test('can toggle auto assignment', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create(['enable_auto_assignment' => false]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'enable_auto_assignment' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.enable_auto_assignment', true);
    });

    test('can update greeting settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create([
            'greeting_enabled' => false,
            'greeting_message' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'greeting_enabled' => true,
                'greeting_message' => 'Welcome!',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.greeting_enabled', true)
            ->assertJsonPath('data.greeting_message', 'Welcome!');
    });
});

describe('Inbox Deletion', function () {
    test('can delete inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}");

        // Rails API returns 200 with message: 'Inbox deletion is in progress'
        $response->assertOk();
        // Note: inbox is soft-deleted so we check trashed
        expect(Inbox::withTrashed()->find($inbox->id)?->trashed() ?? true)->toBeTrue();
    });

    test('deleting non-existent inbox returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/inboxes/99999");

        $response->assertNotFound();
    });
});

describe('Inbox Members', function () {
    test('can list inbox members', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();
        $inbox->users()->attach($agent->id);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/members");

        $response->assertOk();
    });

    test('can add member to inbox', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/members", [
                'user_ids' => [$agent->id],
            ]);

        $response->assertOk();
        expect($inbox->users()->where('users.id', $agent->id)->exists())->toBeTrue();
    });

    test('can remove member from inbox', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();
        $inbox->users()->attach($agent->id);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/members", [
                'user_ids' => [$agent->id],
            ]);

        $response->assertOk();
        expect($inbox->users()->where('users.id', $agent->id)->exists())->toBeFalse();
    });
});

describe('Inbox Authorization', function () {
    test('unauthenticated user cannot list inboxes', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create inbox', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/inboxes", [
            'name' => 'Test Inbox',
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view inboxes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        // User is NOT attached to account

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertNotFound();
    });
});

describe('Inbox Validation', function () {
    test('name is required', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('name cannot be too long', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => str_repeat('a', 300),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Inbox Edge Cases', function () {
    test('inbox with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'サポート 📬',
                'channel_type' => 'Channel::WebWidget',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'サポート 📬');
    });

    test('handles many inboxes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Inbox::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes");

        $response->assertOk();
    });

    test('inbox with long greeting message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $longGreeting = str_repeat('Hello! ', 100);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Long Greeting Inbox',
                'channel_type' => 'Channel::WebWidget',
                'greeting_enabled' => true,
                'greeting_message' => $longGreeting,
            ]);

        $response->assertCreated();
    });
});

describe('Inbox Channel Types', function () {
    test('can create web widget inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Web Widget',
                'channel_type' => 'Channel::WebWidget',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.channel_type', 'Channel::WebWidget');
    });

    test('can create email inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'Email Support',
                'channel_type' => 'Channel::Email',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.channel_type', 'Channel::Email');
    });

    test('can create API inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/inboxes", [
                'name' => 'API Channel',
                'channel_type' => 'Channel::Api',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.channel_type', 'Channel::Api');
    });
});
