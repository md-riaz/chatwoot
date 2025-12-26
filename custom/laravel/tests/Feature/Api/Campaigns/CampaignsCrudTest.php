<?php

/**
 * Comprehensive Campaign API Tests
 *
 * Tests all campaign-related API functionality including CRUD operations,
 * scheduling, audience targeting, and edge cases.
 */

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Inbox;
use App\Models\User;

describe('Campaign Listing', function () {
    test('can list campaigns for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        Campaign::factory(5)->for($account)->for($inbox)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/campaigns");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty campaigns list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/campaigns");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('campaigns list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        Campaign::factory()->for($account)->for($inbox)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/campaigns");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'message',
                        'campaign_type',
                        'campaign_status',
                        'enabled',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Campaign Creation', function () {
    test('can create ongoing campaign', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/campaigns", [
                'title' => 'Welcome Campaign',
                'message' => 'Hello! How can we help you today?',
                'inbox_id' => $inbox->id,
                'campaign_type' => Campaign::TYPE_ONGOING,
                'enabled' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Welcome Campaign')
            ->assertJsonPath('data.campaign_type', Campaign::TYPE_ONGOING)
            ->assertJsonPath('data.enabled', true);
    });

    test('can create one-off campaign', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/campaigns", [
                'title' => 'Announcement',
                'message' => 'We have a new feature!',
                'inbox_id' => $inbox->id,
                'campaign_type' => Campaign::TYPE_ONE_OFF,
                'scheduled_at' => now()->addDays(1)->toIso8601String(),
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.campaign_type', Campaign::TYPE_ONE_OFF);
    });

    test('campaign creation requires title', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/campaigns", [
                'message' => 'Test message',
                'inbox_id' => $inbox->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    test('campaign creation requires inbox_id', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/campaigns", [
                'title' => 'Test Campaign',
                'message' => 'Test message',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['inbox_id']);
    });

    test('campaign with trigger rules', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/campaigns", [
                'title' => 'URL Trigger Campaign',
                'message' => 'Welcome to the pricing page!',
                'inbox_id' => $inbox->id,
                'campaign_type' => Campaign::TYPE_ONGOING,
                'trigger_rules' => [
                    'url' => 'pricing',
                    'time_on_page' => 10,
                ],
            ]);

        $response->assertCreated();
    });
});

describe('Campaign Retrieval', function () {
    test('can show campaign', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $campaign = Campaign::factory()->for($account)->for($inbox)->create(['title' => 'Test Campaign']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/campaigns/{$campaign->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $campaign->id)
            ->assertJsonPath('data.title', 'Test Campaign');
    });

    test('cannot access campaign from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($otherAccount)->create();
        $campaign = Campaign::factory()->for($otherAccount)->for($inbox)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/campaigns/{$campaign->id}");

        $response->assertNotFound();
    });
});

describe('Campaign Update', function () {
    test('can update campaign title', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $campaign = Campaign::factory()->for($account)->for($inbox)->create(['title' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/campaigns/{$campaign->id}", [
                'title' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated');
    });

    test('can toggle campaign enabled status', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $campaign = Campaign::factory()->for($account)->for($inbox)->create(['enabled' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/campaigns/{$campaign->id}", [
                'enabled' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.enabled', false);
    });
});

describe('Campaign Deletion', function () {
    test('can delete campaign', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $campaign = Campaign::factory()->for($account)->for($inbox)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/campaigns/{$campaign->id}");

        $response->assertNoContent();
        expect(Campaign::find($campaign->id))->toBeNull();
    });
});

describe('Campaign Authorization', function () {
    test('unauthenticated user cannot list campaigns', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/campaigns");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create campaign', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/campaigns", [
            'title' => 'Test Campaign',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Campaign Edge Cases', function () {
    test('campaign with unicode title', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/campaigns", [
                'title' => 'ようこそキャンペーン 🎉',
                'message' => 'Welcome!',
                'inbox_id' => $inbox->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'ようこそキャンペーン 🎉');
    });

    test('handles many campaigns', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        Campaign::factory(50)->for($account)->for($inbox)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/campaigns");

        $response->assertOk();
    });
});
