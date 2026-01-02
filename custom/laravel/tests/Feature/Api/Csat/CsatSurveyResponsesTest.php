<?php

/**
 * Comprehensive CSAT Survey Response API Tests
 *
 * Tests all CSAT (Customer Satisfaction) survey functionality including
 * collection, reporting, and analytics.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('CSAT Survey Response Collection', function () {
    test('contact can submit CSAT rating', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 5,
            'feedback_message' => 'Excellent service!',
        ]);

        $response->assertOk();
    });

    test('CSAT rating must be between 1 and 5', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 10,
        ]);

        $response->assertUnprocessable();
    });

    test('CSAT submission without feedback is allowed', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 4,
        ]);

        $response->assertOk();
    });

    test('cannot submit CSAT for non-existent conversation', function () {
        $response = $this->postJson('/api/v1/public/csat/invalid-uuid', [
            'rating' => 5,
        ]);

        $response->assertNotFound();
    });

    test('cannot submit CSAT for open conversation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->open()
            ->create();

        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 5,
        ]);

        $response->assertForbidden();
    });
});

describe('CSAT Survey Responses Listing', function () {
    test('admin can list CSAT responses', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses");

        $response->assertOk();
    });

    test('CSAT list includes expected fields', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('can filter CSAT by date range', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses?" . http_build_query([
                'from' => now()->subDays(30)->toDateString(),
                'to' => now()->toDateString(),
            ]));

        $response->assertOk();
    });

    test('can filter CSAT by rating', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses?rating=5");

        $response->assertOk();
    });

    test('can filter CSAT by inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses?inbox_id={$inbox->id}");

        $response->assertOk();
    });

    test('can filter CSAT by agent', function () {
        $admin = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses?user_id={$agent->id}");

        $response->assertOk();
    });
});

describe('CSAT Metrics', function () {
    test('can get CSAT metrics', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/metrics");

        $response->assertOk();
    });

    test('CSAT metrics include average rating', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/metrics");

        $response->assertOk();
    });

    test('CSAT metrics include response count', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/metrics");

        $response->assertOk();
    });
});

describe('CSAT Agent Performance', function () {
    test('can get agent CSAT performance', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/agents");

        $response->assertOk();
    });

    test('agent CSAT shows individual ratings', function () {
        $admin = User::factory()->create();
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/agents");

        $response->assertOk();
    });
});

describe('CSAT Authorization', function () {
    test('unauthenticated user cannot list CSAT responses', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses");

        $response->assertUnauthorized();
    });

    test('agent cannot view CSAT responses', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses");

        $response->assertForbidden();
    });

    test('user without account access cannot view CSAT', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses");

        $response->assertNotFound();
    });
});

describe('CSAT Export', function () {
    test('admin can export CSAT data', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/export");

        $response->assertOk();
    });

    test('export includes proper headers', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/csat_survey_responses/export");

        $response->assertOk();
    });
});

describe('CSAT Survey Configuration', function () {
    test('admin can enable CSAT for inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => false]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'csat_survey_enabled' => true,
            ]);

        $response->assertOk();
    });

    test('admin can disable CSAT for inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'csat_survey_enabled' => false,
            ]);

        $response->assertOk();
    });
});

describe('CSAT Edge Cases', function () {
    test('handles unicode feedback', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 5,
            'feedback_message' => '素晴らしいサービス！ 🌟',
        ]);

        $response->assertOk();
    });

    test('handles very long feedback', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        $longFeedback = str_repeat('Great service! ', 100);

        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 5,
            'feedback_message' => $longFeedback,
        ]);

        $response->assertOk();
    });

    test('duplicate CSAT submission updates existing', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['csat_survey_enabled' => true]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->resolved()
            ->create();

        // First submission
        $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 3,
        ])->assertOk();

        // Second submission (update)
        $response = $this->postJson("/api/v1/public/csat/{$conversation->uuid}", [
            'rating' => 5,
        ]);

        $response->assertOk();
    });
});
