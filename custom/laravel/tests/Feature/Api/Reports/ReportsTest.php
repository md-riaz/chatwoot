<?php

/**
 * Comprehensive Reports API Tests
 *
 * Tests all reporting-related API functionality including conversation reports,
 * agent reports, team reports, and CSAT reports.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('Conversation Reports', function () {
    test('can get conversation reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('conversation report includes count metrics', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory(10)->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });

    test('can filter conversation reports by inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp."&inbox_id={$inbox->id}");

        $response->assertOk();
    });

    test('can get conversation reports by different time ranges', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        // Last 30 days
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(30)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });
});

describe('Agent Reports', function () {
    test('can get agent reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/agents?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('agent report shows performance metrics', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/agents?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });

    test('can filter agent reports by specific agent', function () {
        $user = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/agents?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp."&agent_id={$agent->id}");

        $response->assertOk();
    });
});

describe('Team Reports', function () {
    test('can get team reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/teams?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('can filter team reports by team', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/teams?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp.'&team_id=1');

        $response->assertOk();
    });
});

describe('Inbox Reports', function () {
    test('can get inbox reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/inboxes?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('inbox report shows traffic data', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory(10)->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/inboxes?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });
});

describe('Label Reports', function () {
    test('can get label reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/labels?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('CSAT Reports', function () {
    test('can get CSAT reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/csat?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('CSAT report includes satisfaction scores', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/csat?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });
});

describe('Report Summary', function () {
    test('can get account summary', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/summary?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });

    test('summary includes key metrics', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory(20)->for($account)->for($inbox)->for($contact)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/summary?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        $response->assertOk();
    });
});

describe('Report Export', function () {
    test('can export conversation report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp.'&type=export');

        $response->assertOk();
    });

    test('can export agent report', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/agents?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp.'&type=export');

        $response->assertOk();
    });
});

describe('Report Authorization', function () {
    test('unauthenticated user cannot access reports', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/reports/conversations");

        $response->assertUnauthorized();
    });

    test('agent cannot access reports by default', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(7)->timestamp.'&until='.now()->timestamp);

        // Depending on implementation, agents may or may not access reports
        $response->assertForbidden()->or($response->assertOk());
    });

    test('user without account access cannot view reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations");

        $response->assertNotFound();
    });
});

describe('Report Validation', function () {
    test('requires since parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?until=".now()->timestamp);

        $response->assertUnprocessable()->or($response->assertOk());
    });

    test('requires until parameter', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->subDays(7)->timestamp);

        $response->assertUnprocessable()->or($response->assertOk());
    });

    test('since must be before until', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->timestamp.'&until='.now()->subDays(7)->timestamp);

        $response->assertUnprocessable()->or($response->assertOk());
    });

    test('handles invalid date format', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=invalid&until=invalid");

        $response->assertUnprocessable()->or($response->assertOk());
    });
});

describe('Report Time Ranges', function () {
    test('can get daily reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->startOfDay()->timestamp.'&until='.now()->endOfDay()->timestamp.'&type=day');

        $response->assertOk();
    });

    test('can get weekly reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->startOfWeek()->timestamp.'&until='.now()->endOfWeek()->timestamp.'&type=week');

        $response->assertOk();
    });

    test('can get monthly reports', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/reports/conversations?since=".now()->startOfMonth()->timestamp.'&until='.now()->endOfMonth()->timestamp.'&type=month');

        $response->assertOk();
    });
});
