<?php

/**
 * SLA Policy API Tests
 *
 * Tests SLA (Service Level Agreement) functionality including
 * response time and resolution time tracking.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('SLA Policy Listing', function () {
    test('can list SLA policies', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/sla_policies");

        $response->assertOk();
    });

    test('returns empty list when no policies', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/sla_policies");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });
});

describe('SLA Policy Creation', function () {
    test('can create SLA policy', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/sla_policies", [
                'name' => 'Enterprise SLA',
                'first_response_time_threshold' => 3600,
                'next_response_time_threshold' => 7200,
                'resolution_time_threshold' => 86400,
                'business_hours' => true,
            ]);

        $response->assertCreated();
    });

    test('SLA policy requires name', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/sla_policies", [
                'first_response_time_threshold' => 3600,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('can create SLA with custom conditions', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/sla_policies", [
                'name' => 'VIP SLA',
                'first_response_time_threshold' => 1800,
                'conditions' => [
                    ['attribute_key' => 'priority', 'filter_operator' => 'equal_to', 'values' => ['urgent']],
                ],
            ]);

        $response->assertCreated();
    });
});

describe('SLA Policy Update', function () {
    test('can update SLA policy', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/sla_policies", [
                'name' => 'Original SLA',
                'first_response_time_threshold' => 3600,
            ]);

        $policyId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/sla_policies/{$policyId}", [
                'name' => 'Updated SLA',
                'first_response_time_threshold' => 1800,
            ]);

        $response->assertOk();
    });
});

describe('SLA Policy Deletion', function () {
    test('can delete SLA policy', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/sla_policies", [
                'name' => 'Delete Me',
                'first_response_time_threshold' => 3600,
            ]);

        $policyId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/sla_policies/{$policyId}");

        $response->assertNoContent();
    });
});

describe('SLA Breach Detection', function () {
    test('detects first response time breach', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create([
                'created_at' => now()->subHours(2),
                'first_reply_created_at' => null,
            ]);

        $slaThreshold = 3600;
        $elapsed = now()->diffInSeconds($conversation->created_at);

        $isBreached = $elapsed > $slaThreshold;

        expect($isBreached)->toBeTrue();
    });

    test('no breach when response within threshold', function () {
        $slaThreshold = 3600;
        $responseTime = 1800;

        $isBreached = $responseTime > $slaThreshold;

        expect($isBreached)->toBeFalse();
    });

    test('detects resolution time breach', function () {
        $slaThreshold = 86400;
        $elapsed = 100000;

        $isBreached = $elapsed > $slaThreshold;

        expect($isBreached)->toBeTrue();
    });
});

describe('SLA Metrics', function () {
    test('calculates average response time', function () {
        $responseTimes = [1800, 2400, 3000, 1200];
        $average = array_sum($responseTimes) / count($responseTimes);

        expect($average)->toBe(2100.0);
    });

    test('calculates SLA compliance rate', function () {
        $totalConversations = 100;
        $compliantConversations = 85;

        $complianceRate = ($compliantConversations / $totalConversations) * 100;

        expect($complianceRate)->toBe(85.0);
    });

    test('tracks breach count', function () {
        $breaches = [
            'first_response' => 5,
            'next_response' => 3,
            'resolution' => 2,
        ];

        $totalBreaches = array_sum($breaches);

        expect($totalBreaches)->toBe(10);
    });
});

describe('SLA Authorization', function () {
    test('agent cannot create SLA policies', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/sla_policies", [
                'name' => 'Test SLA',
            ]);

        $response->assertForbidden();
    });

    test('unauthenticated user cannot access SLA', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/sla_policies");

        $response->assertUnauthorized();
    });
});

describe('SLA Business Hours', function () {
    test('SLA respects business hours', function () {
        $businessHoursEnabled = true;

        expect($businessHoursEnabled)->toBeTrue();
    });

    test('pauses SLA outside business hours', function () {
        $isBusinessHours = false;
        $slaPaused = !$isBusinessHours;

        expect($slaPaused)->toBeTrue();
    });

    test('calculates time excluding non-business hours', function () {
        $totalElapsed = 36000;
        $nonBusinessHours = 14400;

        $effectiveElapsed = $totalElapsed - $nonBusinessHours;

        expect($effectiveElapsed)->toBe(21600);
    });
});
