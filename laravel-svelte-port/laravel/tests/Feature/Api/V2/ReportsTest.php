<?php

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Conversation;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->admin = User::factory()->create();
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->admin->id,
        'role' => AccountUser::ROLE_ADMINISTRATOR,
    ]);
});

test('heatmap data endpoint returns correct format', function () {
    $since = Carbon::now()->subDays(7)->startOfDay();
    $until = Carbon::now()->endOfDay();
    
    // Create some conversations
    Conversation::factory()->count(10)->create([
        'account_id' => $this->account->id,
        'created_at' => Carbon::now()->subDays(2),
    ]);
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/reports?" . http_build_query([
            'metric' => 'conversations_count',
            'group_by' => 'hour',
            'since' => $since->timestamp,
            'until' => $until->timestamp,
            'timezone_offset' => 0,
        ]));
    
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['timestamp', 'value']
            ]
        ]);
    
    $data = $response->json('data');
    
    expect($data)->toBeArray()
        ->and(count($data))->toBeGreaterThan(0)
        ->and($data[0])->toHaveKeys(['timestamp', 'value']);
});

test('heatmap data endpoint validates required parameters', function () {
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/reports");
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['metric', 'group_by', 'since', 'until']);
});

test('heatmap data endpoint validates group_by parameter', function () {
    $since = Carbon::now()->subDays(7)->timestamp;
    $until = Carbon::now()->timestamp;
    
    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/reports?" . http_build_query([
            'metric' => 'conversations_count',
            'group_by' => 'invalid',
            'since' => $since,
            'until' => $until,
        ]));
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['group_by']);
});

test('conversation traffic CSV endpoint returns CSV file', function () {
    // Create some conversations
    Conversation::factory()->count(20)->create([
        'account_id' => $this->account->id,
        'created_at' => Carbon::now()->subDays(3),
    ]);
    
    $response = $this->actingAs($this->admin)
        ->get("/api/v1/accounts/{$this->account->id}/v2/reports/conversation_traffic?" . http_build_query([
            'days_before' => 6,
            'timezone_offset' => 0,
        ]));
    
    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv')
        ->assertHeader('Content-Disposition', 'attachment; filename="conversation_traffic_reports.csv"');
    
    $content = $response->getContent();
    
    // Verify CSV structure
    expect($content)->toContain('Timezone')
        ->and($content)->toContain('Start of the hour');
});

test('conversation traffic CSV endpoint validates parameters', function () {
    $response = $this->actingAs($this->admin)
        ->get("/api/v1/accounts/{$this->account->id}/v2/reports/conversation_traffic?" . http_build_query([
            'days_before' => 500, // Invalid: max 365
        ]));
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['days_before']);
});

test('reports endpoints require authentication', function () {
    $response = $this->getJson("/api/v1/accounts/{$this->account->id}/v2/reports");
    
    $response->assertUnauthorized();
});

test('reports endpoints require admin access', function () {
    $agent = User::factory()->create();
    
    AccountUser::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $agent->id,
        'role' => AccountUser::ROLE_AGENT,
    ]);
    
    $since = Carbon::now()->subDays(7)->timestamp;
    $until = Carbon::now()->timestamp;
    
    $response = $this->actingAs($agent)
        ->getJson("/api/v1/accounts/{$this->account->id}/v2/reports?" . http_build_query([
            'metric' => 'conversations_count',
            'group_by' => 'hour',
            'since' => $since,
            'until' => $until,
        ]));
    
    $response->assertForbidden();
});

test('heatmap data supports different metrics', function () {
    $since = Carbon::now()->subDays(7)->timestamp;
    $until = Carbon::now()->timestamp;
    
    $metrics = ['conversations_count', 'resolutions_count', 'incoming_messages_count', 'outgoing_messages_count'];
    
    foreach ($metrics as $metric) {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/accounts/{$this->account->id}/v2/reports?" . http_build_query([
                'metric' => $metric,
                'group_by' => 'hour',
                'since' => $since,
                'until' => $until,
            ]));
        
        $response->assertOk();
    }
});

test('heatmap data supports different grouping periods', function () {
    $since = Carbon::now()->subDays(30)->timestamp;
    $until = Carbon::now()->timestamp;
    
    $groupings = ['hour', 'day', 'week', 'month'];
    
    foreach ($groupings as $groupBy) {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/accounts/{$this->account->id}/v2/reports?" . http_build_query([
                'metric' => 'conversations_count',
                'group_by' => $groupBy,
                'since' => $since,
                'until' => $until,
            ]));
        
        $response->assertOk();
    }
});
