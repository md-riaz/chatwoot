<?php

/**
 * Comprehensive Reports API V2 Tests
 *
 * Tests all V2 reporting-related API functionality including timeseries reports,
 * summary reports, live reports, and bot metrics.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\ReportingEvent;
use App\Models\Team;
use App\Models\User;

describe('Reports V2 - Timeseries Reports', function () {
    test('can get timeseries report data', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'period' => [
                    'since',
                    'until',
                    'group_by',
                ],
            ]);
    });

    test('can get timeseries report with different grouping', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}&group_by=week");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'period' => [
                    'since',
                    'until',
                    'group_by',
                ],
            ]);
    });

    test('can filter timeseries by agent', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}&type=agent&id={$agent->id}");

        $response->assertOk();
    });

    test('can filter timeseries by inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}&type=inbox&id={$inbox->id}");

        $response->assertOk();
    });
});

describe('Reports V2 - Summary Reports', function () {
    test('can get summary report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/summary?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'conversations_count',
                'incoming_messages_count',
                'outgoing_messages_count',
                'resolutions_count',
                'previous',
            ]);
    });

    test('can get bot summary report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/bot_summary?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'bot_resolutions_count',
                'bot_handoffs_count',
                'bot_resolution_rate',
                'previous',
            ]);
    });
});

describe('Reports V2 - CSV Export Reports', function () {
    test('can export agents report as CSV', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/v1/accounts/{$account->id}/v2/reports/agents?since={$since}&until={$until}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition');
    });

    test('can export inboxes report as CSV', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/v1/accounts/{$account->id}/v2/reports/inboxes?since={$since}&until={$until}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });

    test('can export teams report as CSV', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/v1/accounts/{$account->id}/v2/reports/teams?since={$since}&until={$until}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });

    test('can export labels report as CSV', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/v1/accounts/{$account->id}/v2/reports/labels?since={$since}&until={$until}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });

    test('can export conversation traffic report as CSV', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->get("/api/v1/accounts/{$account->id}/v2/reports/conversation_traffic?since={$since}&until={$until}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });
});

describe('Reports V2 - Conversation Metrics', function () {
    test('can get conversation metrics', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/conversations?type=resolved");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
    });

    test('conversation metrics requires type parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/conversations");

        $response->assertStatus(422)
            ->assertJson(['error' => 'Type parameter is required']);
    });

    test('can filter conversation metrics by user', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/conversations?type=resolved&user_id={$agent->id}");

        $response->assertOk();
    });
});

describe('Reports V2 - Bot Metrics', function () {
    test('can get bot metrics', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/bot_metrics?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'bot_resolutions',
                'bot_handoffs',
                'bot_resolution_rate',
                'avg_bot_resolution_time',
                'bot_conversations',
                'total_bot_interactions',
            ]);
    });
});

describe('Reports V2 - Live Reports', function () {
    test('can get live conversation metrics', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/live_reports/conversation_metrics");

        $response->assertOk()
            ->assertJsonStructure([
                'open',
                'unattended',
                'unassigned',
                'pending',
            ]);
    });

    test('can get grouped conversation metrics', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/live_reports/grouped_conversation_metrics?group_by=team_id");

        $response->assertOk()
            ->assertJsonArray();
    });

    test('grouped conversation metrics validates group_by parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/live_reports/grouped_conversation_metrics?group_by=invalid");

        $response->assertStatus(422)
            ->assertJson(['error' => 'invalid group_by']);
    });

    test('can filter live metrics by team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $team = Team::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/live_reports/conversation_metrics?team_id={$team->id}");

        $response->assertOk();
    });
});

describe('Reports V2 - Summary Reports by Entity', function () {
    test('can get agent summary report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/summary_reports/agent?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'agents',
                'period',
                'business_hours',
            ]);
    });

    test('can get team summary report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/summary_reports/team?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'teams',
                'period',
                'business_hours',
            ]);
    });

    test('can get inbox summary report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/summary_reports/inbox?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'inboxes',
                'period',
                'business_hours',
            ]);
    });

    test('can get label summary report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/summary_reports/label?since={$since}&until={$until}");

        $response->assertOk()
            ->assertJsonStructure([
                'labels',
                'period',
                'business_hours',
                'total_conversations',
            ]);
    });
});

describe('Reports V2 - Validation', function () {
    test('timeseries reports require since parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?until={$until}");

        $response->assertStatus(422);
    });

    test('timeseries reports require until parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}");

        $response->assertStatus(422);
    });

    test('since must be before until', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->toDateString();
        $until = now()->subDays(7)->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}");

        $response->assertStatus(422);
    });

    test('validates group_by parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}&group_by=invalid");

        $response->assertStatus(422);
    });

    test('validates type parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}&type=invalid");

        $response->assertStatus(422);
    });
});

describe('Reports V2 - Authorization', function () {
    test('unauthenticated user cannot access V2 reports', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/v2/reports/");

        $response->assertUnauthorized();
    });

    test('non-admin user cannot access V2 reports', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}");

        $response->assertForbidden();
    });

    test('user without account access cannot view V2 reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}");

        $response->assertNotFound();
    });
});

describe('Reports V2 - Business Hours', function () {
    test('can enable business hours in timeseries reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/reports/?since={$since}&until={$until}&business_hours=true");

        $response->assertOk();
    });

    test('can enable business hours in summary reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $since = now()->subDays(7)->toDateString();
        $until = now()->toDateString();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/v2/summary_reports/agent?since={$since}&until={$until}&business_hours=true");

        $response->assertOk()
            ->assertJson(['business_hours' => true]);
    });
});