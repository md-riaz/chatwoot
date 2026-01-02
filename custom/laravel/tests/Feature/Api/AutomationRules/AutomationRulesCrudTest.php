<?php

/**
 * Comprehensive Automation Rule API Tests
 *
 * Tests all automation rule-related API functionality including CRUD operations,
 * conditions, actions, and edge cases.
 */

use App\Models\Account;
use App\Models\AutomationRule;
use App\Models\User;

describe('Automation Rule Listing', function () {
    test('can list automation rules for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        AutomationRule::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/automation_rules");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty automation rules list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/automation_rules");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('automation rules list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        AutomationRule::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/automation_rules");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'event_name',
                        'conditions',
                        'actions',
                        'active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Automation Rule Creation', function () {
    test('can create automation rule', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'name' => 'Auto Tag VIP',
                'description' => 'Automatically tag VIP contacts',
                'event_name' => 'conversation_created',
                'conditions' => [
                    [
                        'attribute_key' => 'contact_custom_attribute.tier',
                        'filter_operator' => 'equal_to',
                        'values' => ['enterprise'],
                        'query_operator' => 'and',
                    ],
                ],
                'actions' => [
                    [
                        'action_name' => 'add_label',
                        'action_params' => ['vip'],
                    ],
                ],
                'active' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Auto Tag VIP')
            ->assertJsonPath('data.active', true);
    });

    test('automation rule creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'event_name' => 'conversation_created',
                'conditions' => [],
                'actions' => [],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('automation rule creation requires event_name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'name' => 'Test Rule',
                'conditions' => [],
                'actions' => [],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['event_name']);
    });

    test('can create auto-assign rule', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'name' => 'Auto Assign',
                'event_name' => 'conversation_created',
                'conditions' => [
                    [
                        'attribute_key' => 'inbox_id',
                        'filter_operator' => 'equal_to',
                        'values' => [1],
                        'query_operator' => 'and',
                    ],
                ],
                'actions' => [
                    [
                        'action_name' => 'assign_agent',
                        'action_params' => ['auto'],
                    ],
                ],
                'active' => true,
            ]);

        $response->assertCreated();
    });

    test('can create send email rule', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'name' => 'Send Welcome Email',
                'event_name' => 'conversation_created',
                'conditions' => [],
                'actions' => [
                    [
                        'action_name' => 'send_email_to_team',
                        'action_params' => ['1'],
                    ],
                ],
                'active' => true,
            ]);

        $response->assertCreated();
    });
});

describe('Automation Rule Retrieval', function () {
    test('can show automation rule', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($account)->create(['name' => 'Test Rule']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/automation_rules/{$rule->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $rule->id)
            ->assertJsonPath('data.name', 'Test Rule');
    });

    test('cannot access automation rule from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/automation_rules/{$rule->id}");

        // User doesn't have access to otherAccount, middleware returns 404
        $response->assertNotFound();
    });
});

describe('Automation Rule Update', function () {
    test('can update automation rule name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/automation_rules/{$rule->id}", [
                'name' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    });

    test('can toggle active status', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($account)->create(['active' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/automation_rules/{$rule->id}", [
                'active' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.active', false);
    });

    test('can update conditions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($account)->create();

        $newConditions = [
            [
                'attribute_key' => 'status',
                'filter_operator' => 'equal_to',
                'values' => ['open'],
                'query_operator' => 'or',
            ],
            [
                'attribute_key' => 'priority',
                'filter_operator' => 'equal_to',
                'values' => ['high'],
                'query_operator' => 'and',
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/automation_rules/{$rule->id}", [
                'conditions' => $newConditions,
            ]);

        $response->assertOk();
        $rule->refresh();
        expect(count($rule->conditions))->toBe(2);
    });
});

describe('Automation Rule Deletion', function () {
    test('can delete automation rule', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/automation_rules/{$rule->id}");

        $response->assertNoContent();
        expect(AutomationRule::find($rule->id))->toBeNull();
    });

    test('deleting non-existent automation rule returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/automation_rules/99999");

        $response->assertNotFound();
    });
});

describe('Automation Rule Clone', function () {
    test('can clone automation rule', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $rule = AutomationRule::factory()->for($account)->create(['name' => 'Original Rule']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules/{$rule->id}/clone");

        $response->assertCreated();
        expect(AutomationRule::where('account_id', $account->id)->count())->toBe(2);
    });
});

describe('Automation Rule Authorization', function () {
    test('unauthenticated user cannot list automation rules', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/automation_rules");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create automation rule', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
            'name' => 'Test Rule',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Automation Rule Edge Cases', function () {
    test('automation rule with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'name' => '自動化ルール 🤖',
                'event_name' => 'conversation_created',
                'conditions' => [],
                'actions' => [
                    ['action_name' => 'add_label', 'action_params' => ['test']],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', '自動化ルール 🤖');
    });

    test('handles many automation rules', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        AutomationRule::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/automation_rules");

        $response->assertOk();
    });

    test('complex conditions and actions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/automation_rules", [
                'name' => 'Complex Rule',
                'event_name' => 'conversation_created',
                'conditions' => [
                    ['attribute_key' => 'status', 'filter_operator' => 'equal_to', 'values' => ['open'], 'query_operator' => 'and'],
                    ['attribute_key' => 'inbox_id', 'filter_operator' => 'equal_to', 'values' => [1], 'query_operator' => 'and'],
                    ['attribute_key' => 'contact_custom_attribute.tier', 'filter_operator' => 'equal_to', 'values' => ['enterprise'], 'query_operator' => 'and'],
                ],
                'actions' => [
                    ['action_name' => 'add_label', 'action_params' => ['vip']],
                    ['action_name' => 'assign_agent', 'action_params' => ['1']],
                    ['action_name' => 'send_email_to_team', 'action_params' => ['1']],
                ],
                'active' => true,
            ]);

        $response->assertCreated();
    });
});
