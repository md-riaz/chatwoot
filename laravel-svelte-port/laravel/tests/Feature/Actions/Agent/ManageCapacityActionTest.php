<?php

use App\Actions\Agent\ManageCapacityAction;
use App\Models\Account;
use App\Models\AgentCapacityPolicy;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\InboxCapacityLimit;
use App\Models\Label;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ManageCapacityAction - Available Agents', function () {
    test('returns available agents for inbox', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $account->users()->attach($agent->id, ['role' => 1, 'availability' => 'online', 'active_at' => true]);
        $inbox->members()->attach($agent->id);

        $availableAgents = ManageCapacityAction::run()->getAvailableAgents($inbox);

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
            'conversation_limit' => 0, // At capacity
        ]);
        
        $account->users()->attach($agent->id, [
            'role' => 1, 
            'availability' => 'online', 
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id
        ]);
        $inbox->members()->attach($agent->id);

        $availableAgents = ManageCapacityAction::run()->getAvailableAgents($inbox);

        expect($availableAgents)->toHaveCount(0);
    });

    test('excludes agents without inbox access', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        // Agent not attached to account or inbox
        $availableAgents = ManageCapacityAction::run()->getAvailableAgents($inbox);

        expect($availableAgents)->toHaveCount(0);
    });
});

describe('ManageCapacityAction - Conversation Assignment', function () {
    test('excludes conversations with excluded labels', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->create();
        $label = Label::factory()->for($account)->create(['title' => 'excluded']);
        
        $policy = AgentCapacityPolicy::factory()->for($account)->create([
            'exclusion_rules' => ['excluded_labels' => ['excluded']]
        ]);
        
        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online', 
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id
        ]);
        $inbox->members()->attach($agent->id);
        $conversation->labels()->attach($label->id);

        $canTake = ManageCapacityAction::run()->canAgentTakeConversation($agent, $inbox, $conversation);

        expect($canTake)->toBeFalse();
    });

    test('excludes old conversations based on time rules', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        $oldConversation = Conversation::factory()->for($account)->for($inbox)->create([
            'created_at' => now()->subHours(25) // Older than 24 hours
        ]);
        
        $policy = AgentCapacityPolicy::factory()->for($account)->create([
            'exclusion_rules' => ['exclude_older_than_hours' => 24]
        ]);
        
        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id
        ]);
        $inbox->members()->attach($agent->id);

        $canTake = ManageCapacityAction::run()->canAgentTakeConversation($agent, $inbox, $oldConversation);

        expect($canTake)->toBeFalse();
    });

    test('allows conversations that pass exclusion rules', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->create();
        $label = Label::factory()->for($account)->create(['title' => 'allowed']);
        
        $policy = AgentCapacityPolicy::factory()->for($account)->create([
            'exclusion_rules' => ['excluded_labels' => ['excluded']]
        ]);
        
        $account->users()->attach($agent->id, [
            'role' => 1,
            'availability' => 'online',
            'active_at' => true,
            'agent_capacity_policy_id' => $policy->id
        ]);
        $inbox->members()->attach($agent->id);
        $conversation->labels()->attach($label->id);

        $canTake = ManageCapacityAction::run()->canAgentTakeConversation($agent, $inbox, $conversation);

        expect($canTake)->toBeTrue();
    });
});

describe('ManageCapacityAction - Statistics', function () {
    test('returns capacity stats for agent without policy', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $agent = User::factory()->create();
        
        $account->users()->attach($agent->id, ['role' => 1]);
        $inbox->members()->attach($agent->id);

        $stats = ManageCapacityAction::run()->getAgentCapacityStats($agent, $inbox);

        expect($stats['has_capacity_policy'])->toBeFalse();
        expect($stats['current_conversations'])->toBe(0);
        expect($stats['limit'])->toBeNull();
        expect($stats['at_capacity'])->toBeFalse();
    });

    test('returns capacity stats for agent with policy', function () {
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
            'agent_capacity_policy_id' => $policy->id
        ]);
        $inbox->members()->attach($agent->id);

        $stats = ManageCapacityAction::run()->getAgentCapacityStats($agent, $inbox);

        expect($stats['has_capacity_policy'])->toBeTrue();
        expect($stats['limit'])->toBe(5);
        expect($stats['remaining_capacity'])->toBe(5);
        expect($stats['at_capacity'])->toBeFalse();
    });
});

describe('ManageCapacityAction - Validation', function () {
    test('validates exclusion rules correctly', function () {
        $validRules = [
            'overall_capacity' => 10,
            'exclude_older_than_hours' => 24,
            'excluded_labels' => ['urgent', 'vip']
        ];

        $errors = ManageCapacityAction::run()->validateExclusionRules($validRules);

        expect($errors)->toBeEmpty();
    });

    test('returns errors for invalid exclusion rules', function () {
        $invalidRules = [
            'overall_capacity' => -1,
            'exclude_older_than_hours' => 'invalid',
            'excluded_labels' => 'not_array'
        ];

        $errors = ManageCapacityAction::run()->validateExclusionRules($invalidRules);

        expect($errors)->toHaveKey('overall_capacity');
        expect($errors)->toHaveKey('exclude_older_than_hours');
        expect($errors)->toHaveKey('excluded_labels');
    });
});