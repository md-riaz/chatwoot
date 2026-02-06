<?php

use App\Actions\Reports\GetLiveConversationMetricsAction;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Team;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->team = Team::factory()->create(['account_id' => $this->account->id]);
});

test('returns correct conversation metrics for account', function () {
    // Create test conversations
    Conversation::factory()->count(10)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_OPEN,
        'unattended' => true,
    ]);
    
    Conversation::factory()->count(3)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_OPEN,
        'assignee_id' => null,
    ]);
    
    Conversation::factory()->count(7)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_PENDING,
    ]);
    
    $metrics = GetLiveConversationMetricsAction::run($this->account->id);
    
    expect($metrics)->toBeArray()
        ->and($metrics)->toHaveKeys(['open', 'unattended', 'unassigned', 'pending'])
        ->and($metrics['open'])->toBe(18) // 10 + 5 + 3
        ->and($metrics['unattended'])->toBe(5)
        ->and($metrics['unassigned'])->toBe(3)
        ->and($metrics['pending'])->toBe(7);
});

test('filters metrics by team when team_id provided', function () {
    // Create conversations for specific team
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'team_id' => $this->team->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    // Create conversations for other teams
    Conversation::factory()->count(10)->create([
        'account_id' => $this->account->id,
        'team_id' => Team::factory()->create(['account_id' => $this->account->id])->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $metrics = GetLiveConversationMetricsAction::run($this->account->id, $this->team->id);
    
    expect($metrics['open'])->toBe(5);
});

test('returns zero counts when no conversations exist', function () {
    $metrics = GetLiveConversationMetricsAction::run($this->account->id);
    
    expect($metrics['open'])->toBe(0)
        ->and($metrics['unattended'])->toBe(0)
        ->and($metrics['unassigned'])->toBe(0)
        ->and($metrics['pending'])->toBe(0);
});

test('only counts conversations for specified account', function () {
    $otherAccount = Account::factory()->create();
    
    // Create conversations for this account
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    // Create conversations for other account
    Conversation::factory()->count(10)->create([
        'account_id' => $otherAccount->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $metrics = GetLiveConversationMetricsAction::run($this->account->id);
    
    expect($metrics['open'])->toBe(5);
});
