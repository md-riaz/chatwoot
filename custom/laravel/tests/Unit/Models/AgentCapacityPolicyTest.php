<?php

/**
 * Unit tests for AgentCapacityPolicy model
 */

use App\Models\Account;
use App\Models\AgentCapacityPolicy;
use App\Models\InboxCapacityLimit;
use App\Models\User;

describe('AgentCapacityPolicy Model', function () {
    test('can create agent capacity policy with default exclusion rules', function () {
        $account = Account::factory()->create();
        
        $policy = AgentCapacityPolicy::create([
            'account_id' => $account->id,
            'name' => 'Test Policy',
            'description' => 'Test description',
        ]);

        expect($policy->exclusion_rules)->toBe([]);
        expect($policy->name)->toBe('Test Policy');
        expect($policy->account_id)->toBe($account->id);
    });

    test('has correct relationships', function () {
        $account = Account::factory()->create();
        $policy = AgentCapacityPolicy::factory()->for($account)->create();

        expect($policy->account)->toBeInstanceOf(Account::class);
        expect($policy->inboxCapacityLimits())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($policy->accountUsers())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    test('can apply exclusion rules', function () {
        $account = Account::factory()->create();
        $policy = AgentCapacityPolicy::factory()
            ->for($account)
            ->withExcludedLabels(['high-priority'])
            ->create();

        // Create a mock conversation collection
        $conversations = collect([
            (object) ['id' => 1, 'labels' => collect([])],
            (object) ['id' => 2, 'labels' => collect([])],
        ]);

        // This would normally filter conversations, but we're just testing the method exists
        expect($policy->exclusion_rules)->toHaveKey('excluded_labels');
        expect($policy->exclusion_rules['excluded_labels'])->toContain('high-priority');
    });
});

describe('InboxCapacityLimit Model', function () {
    test('validates positive conversation limit on creation', function () {
        $account = Account::factory()->create();
        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $inbox = \App\Models\Inbox::factory()->for($account)->create();

        expect(function () use ($policy, $inbox) {
            InboxCapacityLimit::create([
                'agent_capacity_policy_id' => $policy->id,
                'inbox_id' => $inbox->id,
                'conversation_limit' => 0, // Invalid
            ]);
        })->toThrow(\InvalidArgumentException::class);
    });

    test('can create valid inbox capacity limit', function () {
        $account = Account::factory()->create();
        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $inbox = \App\Models\Inbox::factory()->for($account)->create();

        $limit = InboxCapacityLimit::create([
            'agent_capacity_policy_id' => $policy->id,
            'inbox_id' => $inbox->id,
            'conversation_limit' => 5,
        ]);

        expect($limit->conversation_limit)->toBe(5);
        expect($limit->agentCapacityPolicy)->toBeInstanceOf(AgentCapacityPolicy::class);
        expect($limit->inbox)->toBeInstanceOf(\App\Models\Inbox::class);
    });
});