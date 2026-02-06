<?php

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\User;
use App\Services\OnlineStatusTracker;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->admin = User::factory()->create();
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->admin->id,
        'role' => AccountUser::ROLE_ADMINISTRATOR,
    ]);
});

test('agent status endpoint returns correct format', function () {
    // Create some agents
    $agent1 = User::factory()->create();
    $agent2 = User::factory()->create();
    $agent3 = User::factory()->create();
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $agent1->id,
        'role' => AccountUser::ROLE_AGENT,
    ]);
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $agent2->id,
        'role' => AccountUser::ROLE_AGENT,
    ]);
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $agent3->id,
        'role' => AccountUser::ROLE_AGENT,
    ]);
    
    // Set statuses in Redis
    OnlineStatusTracker::updatePresence($this->account->id, 'User', $agent1->id);
    OnlineStatusTracker::setStatus($this->account->id, $agent1->id, 'online');
    
    OnlineStatusTracker::updatePresence($this->account->id, 'User', $agent2->id);
    OnlineStatusTracker::setStatus($this->account->id, $agent2->id, 'busy');
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/agents/status");
    
    $response->assertOk()
        ->assertJsonStructure([
            'online',
            'busy',
            'offline',
        ]);
    
    $data = $response->json();
    
    expect($data['online'])->toBeGreaterThanOrEqual(1)
        ->and($data['busy'])->toBeGreaterThanOrEqual(1)
        ->and($data['offline'])->toBeGreaterThanOrEqual(0);
});

test('agent status endpoint requires authentication', function () {
    $response = $this->getJson("/api/v1/accounts/{$this->account->id}/v2/agents/status");
    
    $response->assertUnauthorized();
});

test('agent status endpoint requires admin access', function () {
    $agent = User::factory()->create();
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $agent->id,
        'role' => AccountUser::ROLE_AGENT,
    ]);
    
    $response = $this->actingAs($agent)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/agents/status");
    
    $response->assertForbidden();
});
