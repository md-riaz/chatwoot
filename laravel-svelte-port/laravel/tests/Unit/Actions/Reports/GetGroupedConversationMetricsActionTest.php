<?php

use App\Actions\Reports\GetGroupedConversationMetricsAction;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()->create();
});

test('groups metrics by assignee_id correctly', function () {
    $agent1 = User::factory()->create();
    $agent2 = User::factory()->create();
    
    // Create conversations for agent1
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'assignee_id' => $agent1->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    Conversation::factory()->count(2)->create([
        'account_id' => $this->account->id,
        'assignee_id' => $agent1->id,
        'status' => Conversation::STATUS_OPEN,
        'unattended' => true,
    ]);
    
    // Create conversations for agent2
    Conversation::factory()->count(3)->create([
        'account_id' => $this->account->id,
        'assignee_id' => $agent2->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $metrics = GetGroupedConversationMetricsAction::run($this->account->id, 'assignee_id');
    
    expect($metrics)->toBeArray()
        ->and($metrics)->toHaveCount(2);
    
    $agent1Metrics = collect($metrics)->firstWhere('assignee_id', $agent1->id);
    $agent2Metrics = collect($metrics)->firstWhere('assignee_id', $agent2->id);
    
    expect($agent1Metrics['open'])->toBe(7)
        ->and($agent1Metrics['unattended'])->toBe(2)
        ->and($agent2Metrics['open'])->toBe(3);
});

test('groups metrics by team_id correctly', function () {
    $team1 = Team::factory()->create(['account_id' => $this->account->id]);
    $team2 = Team::factory()->create(['account_id' => $this->account->id]);
    
    // Create conversations for team1
    Conversation::factory()->count(8)->create([
        'account_id' => $this->account->id,
        'team_id' => $team1->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    // Create conversations for team2
    Conversation::factory()->count(4)->create([
        'account_id' => $this->account->id,
        'team_id' => $team2->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $metrics = GetGroupedConversationMetricsAction::run($this->account->id, 'team_id');
    
    expect($metrics)->toBeArray()
        ->and($metrics)->toHaveCount(2);
    
    $team1Metrics = collect($metrics)->firstWhere('team_id', $team1->id);
    $team2Metrics = collect($metrics)->firstWhere('team_id', $team2->id);
    
    expect($team1Metrics['open'])->toBe(8)
        ->and($team2Metrics['open'])->toBe(4);
});

test('throws exception for invalid group_by parameter', function () {
    GetGroupedConversationMetricsAction::run($this->account->id, 'invalid_field');
})->throws(\InvalidArgumentException::class, 'Invalid group_by parameter');

test('excludes null group values from results', function () {
    $agent = User::factory()->create();
    
    // Create conversations with assignee
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'assignee_id' => $agent->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    // Create unassigned conversations (should be excluded)
    Conversation::factory()->count(3)->create([
        'account_id' => $this->account->id,
        'assignee_id' => null,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $metrics = GetGroupedConversationMetricsAction::run($this->account->id, 'assignee_id');
    
    expect($metrics)->toHaveCount(1)
        ->and($metrics[0]['assignee_id'])->toBe($agent->id);
});

test('includes group_id in each metric object matching Rails format', function () {
    $team = Team::factory()->create(['account_id' => $this->account->id]);
    
    Conversation::factory()->count(3)->create([
        'account_id' => $this->account->id,
        'team_id' => $team->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $metrics = GetGroupedConversationMetricsAction::run($this->account->id, 'team_id');
    
    expect($metrics[0])->toHaveKeys(['open', 'unattended', 'unassigned', 'team_id'])
        ->and($metrics[0]['team_id'])->toBe($team->id);
});
