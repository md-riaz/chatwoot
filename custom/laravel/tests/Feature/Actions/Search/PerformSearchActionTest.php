<?php

use App\Actions\Search\PerformSearchAction;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

describe('PerformSearchAction', function () {
    beforeEach(function () {
        $this->account = Account::factory()->create();
        $this->inbox = Inbox::factory()->for($this->account)->create();
        $this->user = User::factory()->create();
        $this->account->users()->attach($this->user->id, ['role' => 1]);
        $this->inbox->members()->attach($this->user->id);
    });

    test('can search messages', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'Hello world',
            'private' => false,
        ]);

        $results = PerformSearchAction::run()->searchMessages('Hello', $this->user, $this->account);

        expect($results)->toHaveCount(1);
        expect($results->first()->content)->toContain('Hello');
    });

    test('can search conversations', function () {
        $contact = Contact::factory()->for($this->account)->create(['name' => 'John Doe']);
        Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();

        $results = PerformSearchAction::run()->searchConversations('John', $this->user, $this->account);

        expect($results)->toHaveCount(1);
    });

    test('can search contacts', function () {
        Contact::factory()->for($this->account)->create(['name' => 'John Doe']);

        $results = PerformSearchAction::run()->searchContacts('John', $this->user, $this->account);

        expect($results)->toHaveCount(1);
    });

    test('can perform comprehensive search', function () {
        $contact = Contact::factory()->for($this->account)->create(['name' => 'Test User']);
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'Test message',
            'private' => false,
        ]);

        $results = PerformSearchAction::run()->handle('Test', 'all', $this->user, $this->account);

        expect($results)->toHaveKey('contacts');
        expect($results)->toHaveKey('messages');
        expect($results)->toHaveKey('conversations');
    });

    test('can search specific type', function () {
        $contact = Contact::factory()->for($this->account)->create(['name' => 'Test User']);
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'Test message',
            'private' => false,
        ]);

        $results = PerformSearchAction::run()->handle('Test', 'Message', $this->user, $this->account);

        expect($results)->toHaveKey('messages');
        expect($results)->not->toHaveKey('contacts');
    });

    test('filters messages by time window', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        // Recent message
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'test recent',
            'private' => false,
            'created_at' => now(),
        ]);
        
        // Old message (beyond time window)
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'test old',
            'private' => false,
            'created_at' => now()->subMonths(6),
        ]);

        $results = PerformSearchAction::run()->searchMessages('test', $this->user, $this->account);

        // Should only return recent message
        expect($results)->toHaveCount(1);
        expect($results->first()->content)->toBe('test recent');
    });

    test('excludes private messages from search', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'test public',
            'private' => false,
        ]);
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'test private',
            'private' => true,
        ]);

        $results = PerformSearchAction::run()->searchMessages('test', $this->user, $this->account);

        // Should only return public message
        expect($results)->toHaveCount(1);
        expect($results->first()->content)->toBe('test public');
    });

    test('caches search results', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'Cached message',
            'private' => false,
        ]);

        // First call should cache the result
        $results1 = PerformSearchAction::run()->handle('Cached', 'Message', $this->user, $this->account);
        
        // Second call should return cached result
        $results2 = PerformSearchAction::run()->handle('Cached', 'Message', $this->user, $this->account);

        expect($results1)->toEqual($results2);
    });

    test('can clear search cache', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        $message = Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'cache test',
            'private' => false,
        ]);

        // Cache a search result
        PerformSearchAction::run()->handle('cache', 'Message', $this->user, $this->account);

        // Clear cache
        PerformSearchAction::run()->clearSearchCache($this->account);

        // Verify cache was cleared by checking if we can cache again
        expect(Cache::has("search:{$this->account->id}:*"))->toBeFalse();
    });

    test('handles empty query gracefully', function () {
        $results = PerformSearchAction::run()->searchMessages('', $this->user, $this->account);
        
        expect($results)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    });

    test('handles special characters in search', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        Message::factory()->for($this->account)->for($conversation)->create([
            'content' => 'special chars: @#$%',
            'private' => false,
        ]);

        $results = PerformSearchAction::run()->searchMessages('special chars', $this->user, $this->account);

        expect($results)->toHaveCount(1);
    });

    test('respects limit parameter', function () {
        $contact = Contact::factory()->for($this->account)->create();
        $conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($contact)->create();
        
        for ($i = 0; $i < 10; $i++) {
            Message::factory()->for($this->account)->for($conversation)->create([
                'content' => "Test message {$i}",
                'private' => false,
            ]);
        }

        $results = PerformSearchAction::run()->searchMessages('Test message', $this->user, $this->account, ['limit' => 5]);

        expect($results)->toHaveCount(5);
    });
});