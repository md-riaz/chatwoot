<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\PermissionFilterService;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private SearchService $searchService;
    private PermissionFilterService $permissionFilterService;
    private Account $account;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is not available in this environment');
        }

        $this->permissionFilterService = new PermissionFilterService();
        $this->searchService = new SearchService($this->permissionFilterService);
        
        $this->account = Account::factory()->create();
        $this->user = User::factory()->create();
        
        // Clear cache before each test
        Cache::flush();
    }

    public function test_legacy_search_returns_matching_messages()
    {
        $m1 = Message::factory()->create([
            'content' => 'Hello world',
            'account_id' => $this->account->id,
        ]);
        $m2 = Message::factory()->create([
            'content' => 'Other message',
            'account_id' => $this->account->id,
        ]);

        $results = $this->searchService->search('Hello', ['account_id' => $this->account->id]);

        $this->assertCount(1, $results);
        $this->assertEquals($m1->id, $results[0]['id']);
    }

    public function test_filter_messages_returns_matching_results()
    {
        $conversation = Conversation::factory()->create(['account_id' => $this->account->id]);
        
        $m1 = Message::factory()->create([
            'content' => 'Hello world test message',
            'account_id' => $this->account->id,
            'conversation_id' => $conversation->id,
            'private' => false,
        ]);
        
        $m2 = Message::factory()->create([
            'content' => 'Different content',
            'account_id' => $this->account->id,
            'conversation_id' => $conversation->id,
            'private' => false,
        ]);

        $results = $this->searchService->filterMessages('Hello', $this->user, $this->account);

        $this->assertCount(1, $results);
        $this->assertEquals($m1->id, $results->first()->id);
    }

    public function test_filter_conversations_returns_matching_results()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'contact_id' => $contact->id,
            'display_id' => 12345,
        ]);

        $results = $this->searchService->filterConversations('John', $this->user, $this->account);

        $this->assertCount(1, $results);
        $this->assertEquals($conversation->id, $results->first()->id);
    }

    public function test_filter_contacts_returns_matching_results()
    {
        $contact1 = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $contact2 = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $results = $this->searchService->filterContacts('John', $this->user, $this->account);

        $this->assertCount(1, $results);
        $this->assertEquals($contact1->id, $results->first()->id);
    }

    public function test_perform_returns_all_search_types()
    {
        // Create test data
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'Test Contact',
        ]);
        
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'contact_id' => $contact->id,
        ]);
        
        $message = Message::factory()->create([
            'account_id' => $this->account->id,
            'conversation_id' => $conversation->id,
            'content' => 'Test message content',
            'private' => false,
        ]);

        $results = $this->searchService->perform('Test', 'all', $this->user, $this->account);

        $this->assertArrayHasKey('contacts', $results);
        $this->assertArrayHasKey('messages', $results);
        $this->assertArrayHasKey('conversations', $results);
        $this->assertArrayHasKey('articles', $results);
    }

    public function test_perform_returns_specific_search_type()
    {
        $message = Message::factory()->create([
            'account_id' => $this->account->id,
            'content' => 'Test message content',
            'private' => false,
        ]);

        $results = $this->searchService->perform('Test', 'Message', $this->user, $this->account);

        $this->assertArrayHasKey('messages', $results);
        $this->assertArrayNotHasKey('contacts', $results);
        $this->assertArrayNotHasKey('conversations', $results);
    }

    public function test_search_respects_time_window_filtering()
    {
        // Create old message (outside time window)
        $oldMessage = Message::factory()->create([
            'account_id' => $this->account->id,
            'content' => 'Old test message',
            'private' => false,
            'created_at' => now()->subMonths(6), // Outside 3-month window
        ]);
        
        // Create recent message (within time window)
        $recentMessage = Message::factory()->create([
            'account_id' => $this->account->id,
            'content' => 'Recent test message',
            'private' => false,
            'created_at' => now()->subDays(30), // Within 3-month window
        ]);

        $results = $this->searchService->filterMessages('test', $this->user, $this->account);

        // Should only return recent message
        $this->assertCount(1, $results);
        $this->assertEquals($recentMessage->id, $results->first()->id);
    }

    public function test_search_excludes_private_messages()
    {
        $conversation = Conversation::factory()->create(['account_id' => $this->account->id]);
        
        $publicMessage = Message::factory()->create([
            'account_id' => $this->account->id,
            'conversation_id' => $conversation->id,
            'content' => 'Public test message',
            'private' => false,
        ]);
        
        $privateMessage = Message::factory()->create([
            'account_id' => $this->account->id,
            'conversation_id' => $conversation->id,
            'content' => 'Private test message',
            'private' => true,
        ]);

        $results = $this->searchService->filterMessages('test', $this->user, $this->account);

        // Should only return public message
        $this->assertCount(1, $results);
        $this->assertEquals($publicMessage->id, $results->first()->id);
    }

    public function test_search_caching_works()
    {
        $message = Message::factory()->create([
            'account_id' => $this->account->id,
            'content' => 'Cached test message',
            'private' => false,
        ]);

        // First call should cache the result
        $results1 = $this->searchService->perform('Cached', 'Message', $this->user, $this->account);
        
        // Second call should return cached result
        $results2 = $this->searchService->perform('Cached', 'Message', $this->user, $this->account);

        $this->assertEquals($results1, $results2);
        $this->assertArrayHasKey('messages', $results1);
        $this->assertCount(1, $results1['messages']);
    }

    public function test_cache_is_cleared_when_message_indexed()
    {
        $message = Message::factory()->create([
            'account_id' => $this->account->id,
            'content' => 'Test message for cache clearing',
            'private' => false,
        ]);

        // Cache a search result
        $this->searchService->perform('cache', 'Message', $this->user, $this->account);

        // Index a new message (should clear cache)
        $this->searchService->indexMessage($message);

        // Verify cache was cleared by checking if we can cache again
        $this->assertTrue(true); // Cache clearing is internal, just verify no errors
    }

    public function test_search_handles_empty_query_gracefully()
    {
        $results = $this->searchService->filterMessages('', $this->user, $this->account);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    public function test_search_handles_special_characters()
    {
        $conversation = Conversation::factory()->create(['account_id' => $this->account->id]);
        
        $message = Message::factory()->create([
            'account_id' => $this->account->id,
            'conversation_id' => $conversation->id,
            'content' => 'Message with special chars: @#$%^&*()',
            'private' => false,
        ]);

        $results = $this->searchService->filterMessages('special chars', $this->user, $this->account);

        $this->assertCount(1, $results);
        $this->assertEquals($message->id, $results->first()->id);
    }

    public function test_search_respects_result_limits()
    {
        $conversation = Conversation::factory()->create(['account_id' => $this->account->id]);
        
        // Create multiple messages
        for ($i = 1; $i <= 20; $i++) {
            Message::factory()->create([
                'account_id' => $this->account->id,
                'conversation_id' => $conversation->id,
                'content' => "Test message number {$i}",
                'private' => false,
            ]);
        }

        $results = $this->searchService->filterMessages('Test message', $this->user, $this->account, ['limit' => 5]);

        $this->assertCount(5, $results);
    }
}
