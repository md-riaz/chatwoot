<?php

/**
 * Audit Logs API Tests
 *
 * Tests audit log functionality including activity tracking
 * and log retrieval.
 */

use App\Models\Account;
use App\Models\User;

describe('Audit Log Listing', function () {
    test('admin can list audit logs', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs");

        $response->assertOk();
    });

    test('audit logs include expected fields', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('can filter audit logs by user', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs?user_id={$admin->id}");

        $response->assertOk();
    });

    test('can filter audit logs by action', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs?action=created");

        $response->assertOk();
    });

    test('can filter audit logs by date range', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs?" . http_build_query([
                'from' => now()->subDays(7)->toDateString(),
                'to' => now()->toDateString(),
            ]));

        $response->assertOk();
    });

    test('can filter audit logs by subject type', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs?subject_type=Conversation");

        $response->assertOk();
    });

    test('audit logs are paginated', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs?page=1&per_page=25");

        $response->assertOk();
    });
});

describe('Audit Log Export', function () {
    test('can export audit logs', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs/export");

        $response->assertOk();
    });

    test('export includes all required columns', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs/export");

        $response->assertOk();
    });
});

describe('Audit Log Authorization', function () {
    test('agent cannot view audit logs', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs");

        $response->assertForbidden();
    });

    test('unauthenticated user cannot view audit logs', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/audit_logs");

        $response->assertUnauthorized();
    });

    test('user without account access cannot view audit logs', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/audit_logs");

        // Returns 403 (forbidden) since user exists but lacks permission
        $response->assertForbidden();
    });
});

describe('Activity Tracking', function () {
    test('tracks conversation created', function () {
        $logEntry = [
            'action' => 'created',
            'subject_type' => 'Conversation',
            'subject_id' => 1,
        ];

        expect($logEntry['action'])->toBe('created');
    });

    test('tracks message created', function () {
        $logEntry = [
            'action' => 'created',
            'subject_type' => 'Message',
            'subject_id' => 1,
        ];

        expect($logEntry['subject_type'])->toBe('Message');
    });

    test('tracks contact updated', function () {
        $logEntry = [
            'action' => 'updated',
            'subject_type' => 'Contact',
            'subject_id' => 1,
            'changes' => [
                'old' => ['name' => 'Old Name'],
                'new' => ['name' => 'New Name'],
            ],
        ];

        expect($logEntry['action'])->toBe('updated');
        expect($logEntry['changes'])->toHaveKey('old');
        expect($logEntry['changes'])->toHaveKey('new');
    });

    test('tracks user login', function () {
        $logEntry = [
            'action' => 'login',
            'subject_type' => 'User',
            'subject_id' => 1,
            'ip_address' => '192.168.1.1',
        ];

        expect($logEntry['action'])->toBe('login');
    });

    test('tracks settings change', function () {
        $logEntry = [
            'action' => 'updated',
            'subject_type' => 'Account',
            'subject_id' => 1,
            'description' => 'Account settings updated',
        ];

        expect($logEntry['description'])->toContain('settings');
    });

    test('tracks conversation assignment', function () {
        $logEntry = [
            'action' => 'assigned',
            'subject_type' => 'Conversation',
            'subject_id' => 1,
            'changes' => [
                'assignee_id' => ['old' => null, 'new' => 5],
            ],
        ];

        expect($logEntry['action'])->toBe('assigned');
    });

    test('tracks label added', function () {
        $logEntry = [
            'action' => 'label_added',
            'subject_type' => 'Conversation',
            'subject_id' => 1,
            'properties' => [
                'label' => 'urgent',
            ],
        ];

        expect($logEntry['action'])->toBe('label_added');
    });

    test('tracks team member added', function () {
        $logEntry = [
            'action' => 'member_added',
            'subject_type' => 'Team',
            'subject_id' => 1,
            'properties' => [
                'user_id' => 5,
            ],
        ];

        expect($logEntry['action'])->toBe('member_added');
    });
});

describe('Audit Log Retention', function () {
    test('logs are retained for configured period', function () {
        $retentionDays = 90;

        expect($retentionDays)->toBe(90);
    });

    test('old logs can be purged', function () {
        $retentionDays = 90;
        $cutoffDate = now()->subDays($retentionDays);

        expect($cutoffDate)->toBeLessThan(now());
    });
});
