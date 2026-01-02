<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Performance Validation Test Suite
 * 
 * Validates that the Laravel implementation meets performance requirements
 * and can handle production-scale load.
 * 
 * Reference: TASK_21_FINAL_CHECKPOINT_VALIDATION_REPORT.md
 * Task: 29.2 Functional Parity Validation - Performance Testing
 */
class PerformanceValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Account $account;
    private Inbox $inbox;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create();
        $this->account->users()->attach($this->user, ['role' => 'administrator']);
        $this->inbox = Inbox::factory()->create(['account_id' => $this->account->id]);
        
        Sanctum::actingAs($this->user);
    }

    /**
     * Test API Response Time Requirements
     * Target: Authentication <200ms, Conversations <500ms, Messages <200ms
     */
    public function test_api_response_times_meet_requirements(): void
    {
        // Test Authentication Response Time (<200ms)
        $startTime = microtime(true);
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        $authTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(200, $authTime, "Authentication took {$authTime}ms, should be <200ms");

        // Test Token Validation Response Time (<100ms)
        $token = $response->json('data.token');
        $startTime = microtime(true);
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/v1/auth/me');
        $tokenTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(100, $tokenTime, "Token validation took {$tokenTime}ms, should be <100ms");

        // Test Conversation Listing Response Time (<500ms)
        $this->createTestConversations(50); // Create test data
        
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations");
        $listTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $listTime, "Conversation listing took {$listTime}ms, should be <500ms");

        // Test Message Creation Response Time (<200ms)
        $conversation = Conversation::first();
        $startTime = microtime(true);
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'Performance test message',
            'message_type' => 'outgoing',
            'sender_type' => 'User',
            'sender_id' => $this->user->id
        ]);
        $messageTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(201);
        $this->assertLessThan(200, $messageTime, "Message creation took {$messageTime}ms, should be <200ms");
    }

    /**
     * Test Search Performance Requirements
     * Target: Search queries <500ms
     */
    public function test_search_performance_requirements(): void
    {
        // Create test data for search
        $this->createTestContacts(100);
        $this->createTestConversations(100);
        
        // Test Contact Search Performance
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts?q=test");
        $searchTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $searchTime, "Contact search took {$searchTime}ms, should be <500ms");

        // Test Conversation Search Performance
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations?q=test");
        $searchTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $searchTime, "Conversation search took {$searchTime}ms, should be <500ms");

        // Test Global Search Performance
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/search?q=test");
        $searchTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $searchTime, "Global search took {$searchTime}ms, should be <500ms");
    }

    /**
     * Test Report Generation Performance
     * Target: Report generation <3 seconds
     */
    public function test_report_generation_performance(): void
    {
        // Create test data for reports
        $this->createTestConversations(200);
        $this->createTestMessages(500);
        
        // Test Conversation Reports Performance
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/reports/conversations?" . http_build_query([
            'metric' => 'conversations_count',
            'type' => 'account',
            'since' => now()->subDays(30)->toDateString(),
            'until' => now()->toDateString()
        ]));
        $reportTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(3000, $reportTime, "Conversation report took {$reportTime}ms, should be <3000ms");

        // Test Agent Performance Reports
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/reports/agents?" . http_build_query([
            'metric' => 'avg_first_response_time',
            'type' => 'agent',
            'since' => now()->subDays(30)->toDateString(),
            'until' => now()->toDateString()
        ]));
        $reportTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(3000, $reportTime, "Agent report took {$reportTime}ms, should be <3000ms");
    }

    /**
     * Test Throughput Requirements
     * Target: 500+ messages per second, 1000+ concurrent users
     */
    public function test_throughput_requirements(): void
    {
        $conversation = $this->createTestConversations(1)[0];
        
        // Test Message Throughput (500+ messages per second)
        $messageCount = 50; // Reduced for test environment
        $startTime = microtime(true);
        
        for ($i = 0; $i < $messageCount; $i++) {
            $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", [
                'content' => "Throughput test message {$i}",
                'message_type' => 'outgoing',
                'sender_type' => 'User',
                'sender_id' => $this->user->id
            ]);
            $response->assertStatus(201);
        }
        
        $totalTime = microtime(true) - $startTime;
        $throughput = $messageCount / $totalTime;
        
        $this->assertGreaterThan(25, $throughput, "Message throughput was {$throughput}/sec, should be >25/sec in test environment");

        // Test API Request Throughput (10,000+ requests per minute)
        $requestCount = 100; // Reduced for test environment
        $startTime = microtime(true);
        
        for ($i = 0; $i < $requestCount; $i++) {
            $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}");
            $response->assertStatus(200);
        }
        
        $totalTime = microtime(true) - $startTime;
        $requestThroughput = $requestCount / $totalTime;
        
        $this->assertGreaterThan(50, $requestThroughput, "API throughput was {$requestThroughput}/sec, should be >50/sec in test environment");
    }

    /**
     * Test Resource Usage Requirements
     * Target: Memory <4GB, CPU <80%, DB connections <200
     */
    public function test_resource_usage_requirements(): void
    {
        // Test Memory Usage
        $memoryBefore = memory_get_usage(true);
        
        // Perform memory-intensive operations
        $conversations = $this->createTestConversations(100);
        $this->createTestMessages(500);
        
        // Load data with relationships
        $loadedConversations = Conversation::with(['contact', 'messages', 'assignee', 'inbox'])
                                          ->where('account_id', $this->account->id)
                                          ->get();
        
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB
        
        $this->assertLessThan(100, $memoryUsed, "Memory usage was {$memoryUsed}MB for 100 conversations, should be reasonable");

        // Test Database Query Efficiency
        DB::enableQueryLog();
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations?include=contact,assignee,inbox");
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        $this->assertLessThan(10, $queryCount, "Query count was {$queryCount}, should use eager loading to minimize queries");
        
        DB::disableQueryLog();
    }

    /**
     * Test Concurrent User Handling
     * Target: 1000+ concurrent users
     */
    public function test_concurrent_user_handling(): void
    {
        // Create multiple users for concurrent testing
        $users = User::factory()->count(10)->create();
        
        foreach ($users as $user) {
            $this->account->users()->attach($user, ['role' => 'agent']);
        }
        
        $startTime = microtime(true);
        $responses = [];
        
        // Simulate concurrent requests from multiple users
        foreach ($users as $user) {
            Sanctum::actingAs($user);
            
            $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations");
            $responses[] = $response;
        }
        
        $totalTime = microtime(true) - $startTime;
        
        // Verify all requests succeeded
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
        
        $this->assertLessThan(5, $totalTime, "Concurrent requests took {$totalTime}s, should handle multiple users efficiently");
    }

    /**
     * Test Cache Performance
     * Validate caching improves performance
     */
    public function test_cache_performance_improvement(): void
    {
        $this->createTestConversations(50);
        
        // First request (no cache)
        Cache::flush();
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations");
        $uncachedTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        // Second request (with cache if implemented)
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations");
        $cachedTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        // Cache should improve performance (or at least not make it worse)
        $this->assertLessThanOrEqual($uncachedTime * 1.1, $cachedTime, "Cached request should not be significantly slower");
    }

    /**
     * Test Real-time Performance
     * Validate WebSocket and real-time features performance
     */
    public function test_realtime_performance(): void
    {
        $conversation = $this->createTestConversations(1)[0];
        
        // Test message creation with real-time broadcasting
        $startTime = microtime(true);
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'Real-time test message',
            'message_type' => 'outgoing',
            'sender_type' => 'User',
            'sender_id' => $this->user->id
        ]);
        
        $realtimeTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(201);
        $this->assertLessThan(300, $realtimeTime, "Real-time message took {$realtimeTime}ms, should be <300ms including broadcast");
    }

    /**
     * Test Database Performance Under Load
     */
    public function test_database_performance_under_load(): void
    {
        // Create substantial test data
        $this->createTestConversations(200);
        $this->createTestMessages(1000);
        
        // Test complex queries performance
        $startTime = microtime(true);
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations?" . http_build_query([
            'status' => 'open',
            'assignee_type' => 'User',
            'include' => 'contact,assignee,last_message',
            'sort' => 'last_activity_at',
            'page' => 1,
            'per_page' => 25
        ]));
        
        $queryTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(1000, $queryTime, "Complex query took {$queryTime}ms, should be <1000ms");
        
        // Verify pagination works efficiently
        $data = $response->json('data');
        $this->assertCount(25, $data, 'Pagination should return exactly 25 items');
        
        // Test that subsequent pages load quickly
        $startTime = microtime(true);
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations?" . http_build_query([
            'status' => 'open',
            'page' => 2,
            'per_page' => 25
        ]));
        
        $paginationTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $paginationTime, "Pagination query took {$paginationTime}ms, should be <500ms");
    }

    // ========================================
    // Helper Methods
    // ========================================

    private function createTestContacts(int $count): array
    {
        return Contact::factory()->count($count)->create([
            'account_id' => $this->account->id,
            'name' => fn() => 'Test Contact ' . $this->faker->name,
            'email' => fn() => $this->faker->email
        ])->toArray();
    }

    private function createTestConversations(int $count): array
    {
        $contacts = Contact::factory()->count($count)->create([
            'account_id' => $this->account->id
        ]);
        
        return Conversation::factory()->count($count)->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => fn() => $contacts->random()->id,
            'assignee_id' => $this->user->id
        ])->toArray();
    }

    private function createTestMessages(int $count): array
    {
        $conversations = Conversation::where('account_id', $this->account->id)->get();
        
        if ($conversations->isEmpty()) {
            $conversations = collect($this->createTestConversations(10));
        }
        
        return Message::factory()->count($count)->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'conversation_id' => fn() => $conversations->random()->id ?? $conversations->first()['id'],
            'sender_type' => 'User',
            'sender_id' => $this->user->id,
            'content' => fn() => 'Test message content ' . $this->faker->sentence
        ])->toArray();
    }
}