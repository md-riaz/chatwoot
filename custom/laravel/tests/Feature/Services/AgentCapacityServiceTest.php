<?php

/**
 * Agent Capacity Service Tests
 *
 * Tests the capacity tracking and enforcement logic for agent assignment.
 */

use App\Models\Account;
use App\Models\AgentCapacityPolicy;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\InboxCapacityLimit;
use App\Models\User;
use App\Services\AgentCapacityService;

describe('Agent Capacity Service - Available Agents', function () {
    test('returns agents without capacity policy as available', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $account->users()->attach($agent->id, ['role' => 1, 'availability' => 'online', 'active_at' => true]);
        $inbox->members()->attach($agent->id);

        $service = new AgentCapacityService();
        $availableAgents = $service->getAvailableAgents($inbox);

        expect($availableAgents)->toHaveCount(1);
        expect($availableAgents->first()->id)->toBe($agent->id);
    });

    test('excludes agents at capacity limit', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $limit = InboxCapacityLimit::factory()->create([
            'agent_capacity_policy_id' => $policy->id,
            'inbox_id' => $inbox->id,
            'conversation_limit' => 2,
        ]);

        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id,
        ]);
        $inbox->members()->attach($agent->id);

        // Create conversations to reach capacity
        Conversation::factory(2)->create([
            'inbox_id' => $inbox->id,
            'assignee_id' => $agent->id,
            'status' => 'open',
        ]);

        $service = new AgentCapacityService();
        $availableAgents = $service->getAvailableAgents($inbox);

        expect($availableAgents)->toHaveCount(0);
    });

    test('includes agents below capacity limit', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $limit = InboxCapacityLimit::factory()->create([
            'agent_capacity_policy_id' => $policy->id,
            'inbox_id' => $inbox->id,
            'conversation_limit' => 3,
        ]);

        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id,
        ]);
        $inbox->members()->attach($agent->id);

        // Create conversations below capacity
        Conversation::factory(1)->create([
            'inbox_id' => $inbox->id,
            'assignee_id' => $agent->id,
            'status' => 'open',
        ]);

        $service = new AgentCapacityService();
        $availableAgents = $service->getAvailableAgents($inbox);

        expect($availableAgents)->toHaveCount(1);
        expect($availableAgents->first()->id)->toBe($agent->id);
    });
});

describe('Agent Capacity Service - Exclusion Rules', function () {
    test('excludes conversations with excluded labels', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        $conversation = Conversation::factory()->for($inbox)->create();
        
        $policy = AgentCapacityPolicy::factory()->for($account)->withExcludedLabels(['high-priority'])->create();

        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id,
        ]);

        // Add excluded label to conversation
        $label = \App\Models\Label::factory()->for($account)->create(['title' => 'high-priority']);
        $conversation->labels()->attach($label->id);

        $service = new AgentCapacityService();
        $canTake = $service->canAgentTakeConversation($agent, $inbox, $conversation);

        expect($canTake)->toBeFalse();
    });

    test('excludes conversations older than specified hours', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $policy = AgentCapacityPolicy::factory()->for($account)->withTimeExclusion(24)->create();

        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id,
        ]);

        // Create old conversation
        $oldConversation = Conversation::factory()->for($inbox)->create([
            'created_at' => now()->subHours(25),
        ]);

        $service = new AgentCapacityService();
        $canTake = $service->canAgentTakeConversation($agent, $inbox, $oldConversation);

        expect($canTake)->toBeFalse();
    });

    test('allows conversations that pass exclusion rules', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        $conversation = Conversation::factory()->for($inbox)->create();
        
        $policy = AgentCapacityPolicy::factory()->for($account)->withExcludedLabels(['high-priority'])->create();

        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id,
        ]);

        // Add non-excluded label to conversation
        $label = \App\Models\Label::factory()->for($account)->create(['title' => 'normal']);
        $conversation->labels()->attach($label->id);

        $service = new AgentCapacityService();
        $canTake = $service->canAgentTakeConversation($agent, $inbox, $conversation);

        expect($canTake)->toBeTrue();
    });
});

describe('Agent Capacity Service - Statistics', function () {
    test('returns correct capacity statistics', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $policy = AgentCapacityPolicy::factory()->for($account)->create();
        $limit = InboxCapacityLimit::factory()->create([
            'agent_capacity_policy_id' => $policy->id,
            'inbox_id' => $inbox->id,
            'conversation_limit' => 5,
        ]);

        $account->users()->attach($agent->id, [
            'role' => 1,
            'agent_capacity_policy_id' => $policy->id,
        ]);

        // Create some conversations
        Conversation::factory(2)->create([
            'inbox_id' => $inbox->id,
            'assignee_id' => $agent->id,
            'status' => 'open',
        ]);

        $service = new AgentCapacityService();
        $stats = $service->getAgentCapacityStats($agent, $inbox);

        expect($stats)->toMatchArray([
            'has_capacity_policy' => true,
            'current_conversations' => 2,
            'limit' => 5,
            'remaining_capacity' => 3,
            'at_capacity' => false,
        ]);
    });

    test('returns statistics for agent without capacity policy', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();

        $account->users()->attach($agent->id, ['role' => 1]);

        // Create some conversations
        Conversation::factory(3)->create([
            'inbox_id' => $inbox->id,
            'assignee_id' => $agent->id,
            'status' => 'open',
        ]);

        $service = new AgentCapacityService();
        $stats = $service->getAgentCapacityStats($agent, $inbox);

        expect($stats)->toMatchArray([
            'has_capacity_policy' => false,
            'current_conversations' => 3,
            'limit' => null,
            'remaining_capacity' => null,
            'at_capacity' => false,
        ]);
    });
});

describe('Agent Capacity Service - Validation', function () {
    test('validates exclusion rules correctly', function () {
        $service = new AgentCapacityService();

        $validRules = [
            'overall_capacity' => 10,
            'exclude_older_than_hours' => 24,
            'excluded_labels' => ['high-priority', 'vip'],
        ];

        $errors = $service->validateExclusionRules($validRules);
        expect($errors)->toBeEmpty();
    });

    test('returns errors for invalid exclusion rules', function () {
        $service = new AgentCapacityService();

        $invalidRules = [
            'overall_capacity' => -5,
            'exclude_older_than_hours' => 'invalid',
            'excluded_labels' => 'not_an_array',
        ];

        $errors = $service->validateExclusionRules($invalidRules);
        
        expect($errors)->toHaveKey('overall_capacity');
        expect($errors)->toHaveKey('exclude_older_than_hours');
        expect($errors)->toHaveKey('excluded_labels');
    });
});