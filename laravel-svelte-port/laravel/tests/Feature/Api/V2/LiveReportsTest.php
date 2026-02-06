<?php

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->admin = User::factory()->create();
    
    // Create account user relationship with administrator role
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->admin->id,
        'role' => AccountUser::ROLE_ADMINISTRATOR,
    ]);
});

test('conversation metrics endpoint returns correct format', function () {
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/conversation_metrics");
    
    $response->assertOk()
        ->assertJsonStructure([
            'open',
            'unattended',
            'unassigned',
            'pending',
        ])
        ->assertJson([
            'open' => 5,
        ]);
});

test('conversation metrics endpoint filters by team', function () {
    $team = Team::factory()->create(['account_id' => $this->account->id]);
    
    Conversation::factory()->count(3)->create([
        'account_id' => $this->account->id,
        'team_id' => $team->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    Conversation::factory()->count(7)->create([
        'account_id' => $this->account->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/conversation_metrics?team_id={$team->id}");
    
    $response->assertOk()
        ->assertJson([
            'open' => 3,
        ]);
});

test('grouped conversation metrics endpoint groups by assignee', function () {
    $agent1 = User::factory()->create();
    $agent2 = User::factory()->create();
    
    Conversation::factory()->count(5)->create([
        'account_id' => $this->account->id,
        'assignee_id' => $agent1->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    Conversation::factory()->count(3)->create([
        'account_id' => $this->account->id,
        'assignee_id' => $agent2->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/grouped_conversation_metrics?group_by=assignee_id");
    
    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonStructure([
            '*' => ['open', 'unattended', 'unassigned', 'assignee_id']
        ]);
    
    $data = $response->json();
    $agent1Data = collect($data)->firstWhere('assignee_id', $agent1->id);
    
    expect($agent1Data['open'])->toBe(5);
});

test('grouped conversation metrics endpoint groups by team', function () {
    $team1 = Team::factory()->create(['account_id' => $this->account->id]);
    $team2 = Team::factory()->create(['account_id' => $this->account->id]);
    
    Conversation::factory()->count(8)->create([
        'account_id' => $this->account->id,
        'team_id' => $team1->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    Conversation::factory()->count(4)->create([
        'account_id' => $this->account->id,
        'team_id' => $team2->id,
        'status' => Conversation::STATUS_OPEN,
    ]);
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/grouped_conversation_metrics?group_by=team_id");
    
    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonStructure([
            '*' => ['open', 'unattended', 'unassigned', 'team_id']
        ]);
});

test('grouped conversation metrics endpoint returns 422 for invalid group_by', function () {
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/grouped_conversation_metrics?group_by=invalid");
    
    $response->assertStatus(422)
        ->assertJson([
            'error' => 'invalid group_by',
        ]);
});

test('live reports endpoints require authentication', function () {
    $response = $this->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/conversation_metrics");
    
    $response->assertUnauthorized();
});

test('live reports endpoints require admin access', function () {
    $agent = User::factory()->create();
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $agent->id,
        'role' => AccountUser::ROLE_AGENT,
    ]);
    
    $response = $this->actingAs($agent)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/live_reports/conversation_metrics");
    
    $response->assertForbidden();
});
