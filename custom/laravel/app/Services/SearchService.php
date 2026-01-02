<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Article;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Search service with performance optimizations and full-text search support.
 * Supports multiple search strategies: LIKE, GIN (PostgreSQL), and FULLTEXT (MySQL).
 */
class SearchService
{
    private PermissionFilterService $permissionFilterService;
    private array $config;

    public function __construct(PermissionFilterService $permissionFilterService)
    {
        $this->permissionFilterService = $permissionFilterService;
        $this->config = config('search', [
            'default_per_page' => 15,
            'max_per_page' => 100,
            'time_window_months' => 3,
            'enable_gin_search' => env('SEARCH_ENABLE_GIN', true),
            'enable_advanced_search' => env('SEARCH_ENABLE_ADVANCED', false),
            'cache_ttl' => 300, // 5 minutes
        ]);
    }

    /**
     * Perform comprehensive search across all types or specific type.
     */
    public function perform(
        string $searchQuery,
        string $searchType,
        User $user,
        Account $account,
        array $params = []
    ): array {
        $cacheKey = $this->generateCacheKey($searchQuery, $searchType, $user->id, $account->id, $params);
        
        return Cache::remember($cacheKey, $this->config['cache_ttl'], function () use ($searchQuery, $searchType, $user, $account, $params) {
            return match ($searchType) {
                'Message' => ['messages' => $this->filterMessages($searchQuery, $user, $account, $params)],
                'Conversation' => ['conversations' => $this->filterConversations($searchQuery, $user, $account, $params)],
                'Contact' => ['contacts' => $this->filterContacts($searchQuery, $user, $account, $params)],
                'Article' => ['articles' => $this->filterArticles($searchQuery, $user, $account, $params)],
                default => [
                    'contacts' => $this->filterContacts($searchQuery, $user, $account, $params),
                    'messages' => $this->filterMessages($searchQuery, $user, $account, $params),
                    'conversations' => $this->filterConversations($searchQuery, $user, $account, $params),
                    'articles' => $this->filterArticles($searchQuery, $user, $account, $params),
                ]
            };
        });
    }

    /**
     * Filter messages with optimized search strategies.
     */
    public function filterMessages(string $searchQuery, User $user, Account $account, array $params = []): Collection
    {
        $query = $this->messageBaseQuery($account);
        
        // Apply full-text search or fallback to LIKE
        if ($this->useGinSearch()) {
            $query = $this->searchMessagesWithGin($query, $searchQuery);
        } else {
            $query = $this->searchMessagesWithLike($query, $searchQuery);
        }

        // Apply permission-based filtering
        if (!$this->permissionFilterService->shouldSkipInboxFiltering($user, $account)) {
            $accessibleInboxIds = $this->permissionFilterService->getAccessibleInboxIds($user, $account);
            if ($accessibleInboxIds->isEmpty()) {
                return collect();
            }
            $query->whereHas('conversation', function ($conversationQuery) use ($accessibleInboxIds) {
                $conversationQuery->whereIn('inbox_id', $accessibleInboxIds);
            });
        }

        return $query
            ->with(['conversation', 'sender'])
            ->orderBy('created_at', 'desc')
            ->limit($params['limit'] ?? $this->config['default_per_page'])
            ->get();
    }

    /**
     * Filter conversations with optimized queries.
     */
    public function filterConversations(string $searchQuery, User $user, Account $account, array $params = []): Collection
    {
        $accessibleInboxIds = $this->permissionFilterService->getAccessibleInboxIds($user, $account);
        
        if ($accessibleInboxIds->isEmpty() && !$this->permissionFilterService->shouldSkipInboxFiltering($user, $account)) {
            return collect();
        }

        $query = Conversation::where('account_id', $account->id)
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->select('conversations.*')
            ->whereRaw(
                "CAST(conversations.display_id AS TEXT) ILIKE ? OR contacts.name ILIKE ? OR contacts.email ILIKE ? OR contacts.phone_number ILIKE ? OR contacts.identifier ILIKE ?",
                array_fill(0, 5, "%{$searchQuery}%")
            );

        // Apply inbox filtering if needed
        if (!$this->permissionFilterService->shouldSkipInboxFiltering($user, $account)) {
            $query->whereIn('conversations.inbox_id', $accessibleInboxIds);
        }

        return $query
            ->with(['contact', 'inbox', 'assignee'])
            ->orderBy('conversations.created_at', 'desc')
            ->limit($params['limit'] ?? $this->config['default_per_page'])
            ->get();
    }

    /**
     * Filter contacts with permission-based access.
     */
    public function filterContacts(string $searchQuery, User $user, Account $account, array $params = []): Collection
    {
        $accessibleInboxIds = $this->permissionFilterService->getAccessibleInboxIds($user, $account);
        
        if ($accessibleInboxIds->isEmpty() && !$this->permissionFilterService->shouldSkipInboxFiltering($user, $account)) {
            return collect();
        }

        $query = Contact::where('account_id', $account->id)
            ->where(function ($q) use ($searchQuery) {
                $q->where('name', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('email', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('phone_number', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('identifier', 'ILIKE', "%{$searchQuery}%");
            });

        // Apply inbox filtering through contact_inboxes if needed
        if (!$this->permissionFilterService->shouldSkipInboxFiltering($user, $account)) {
            $query->whereHas('contactInboxes', function ($contactInboxQuery) use ($accessibleInboxIds) {
                $contactInboxQuery->whereIn('inbox_id', $accessibleInboxIds);
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->limit($params['limit'] ?? $this->config['default_per_page'])
            ->get();
    }

    /**
     * Filter articles with full-text search support.
     */
    public function filterArticles(string $searchQuery, User $user, Account $account, array $params = []): Collection
    {
        // Check if Article model exists
        if (!class_exists(Article::class)) {
            return collect();
        }

        $query = Article::where('account_id', $account->id);

        // Apply full-text search for articles
        if ($this->useGinSearch() && DB::getDriverName() === 'pgsql') {
            $tsquery = $this->prepareTsQuery($searchQuery);
            $query->whereRaw(
                "(to_tsvector('english', title) @@ to_tsquery(?) OR to_tsvector('english', content) @@ to_tsquery(?))",
                [$tsquery, $tsquery]
            );
        } elseif (DB::getDriverName() === 'mysql') {
            $query->whereRaw(
                "MATCH(title, content) AGAINST(? IN BOOLEAN MODE)",
                [$searchQuery]
            );
        } else {
            // Fallback to LIKE search
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('content', 'ILIKE', "%{$searchQuery}%");
            });
        }

        return $query
            ->where('status', 'published') // Only published articles
            ->orderBy('created_at', 'desc')
            ->limit($params['limit'] ?? $this->config['default_per_page'])
            ->get();
    }

    /**
     * Get optimized base query for messages with time-based filtering.
     */
    private function messageBaseQuery(Account $account): Builder
    {
        return Message::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subMonths($this->config['time_window_months']))
            ->where('private', false); // Exclude private notes from search
    }

    /**
     * Search messages using PostgreSQL GIN index.
     */
    private function searchMessagesWithGin(Builder $query, string $searchQuery): Builder
    {
        $tsquery = $this->prepareTsQuery($searchQuery);
        
        return $query->whereRaw('to_tsvector(\'english\', content) @@ to_tsquery(?)', [$tsquery]);
    }

    /**
     * Search messages using LIKE queries (fallback).
     */
    private function searchMessagesWithLike(Builder $query, string $searchQuery): Builder
    {
        return $query->where('content', 'ILIKE', "%{$searchQuery}%");
    }

    /**
     * Prepare tsquery for PostgreSQL full-text search.
     */
    private function prepareTsQuery(string $searchQuery): string
    {
        // Split search query and join with phrase search operator
        $terms = explode(' ', trim($searchQuery));
        $cleanTerms = array_map(function ($term) {
            // Remove special characters and escape for tsquery
            return preg_replace('/[^\w\s]/', '', $term);
        }, $terms);
        
        return implode(' <-> ', array_filter($cleanTerms));
    }

    /**
     * Check if GIN search should be used.
     */
    private function useGinSearch(): bool
    {
        return $this->config['enable_gin_search'] && 
               DB::getDriverName() === 'pgsql' && 
               $this->ginIndexExists();
    }

    /**
     * Check if GIN index exists for messages.
     */
    private function ginIndexExists(): bool
    {
        try {
            $result = DB::select("
                SELECT 1 FROM pg_indexes 
                WHERE tablename = 'messages' 
                AND indexname = 'messages_content_fulltext_idx'
            ");
            return !empty($result);
        } catch (\Exception $e) {
            Log::warning('Failed to check GIN index existence', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate cache key for search results.
     */
    private function generateCacheKey(string $searchQuery, string $searchType, int $userId, int $accountId, array $params): string
    {
        $paramsHash = md5(serialize($params));
        return "search:{$accountId}:{$userId}:{$searchType}:" . md5($searchQuery) . ":{$paramsHash}";
    }

    /**
     * Clear search cache for account.
     */
    public function clearSearchCache(Account $account): void
    {
        $pattern = "search:{$account->id}:*";
        // Note: This is a simplified cache clearing. In production, you might want to use Redis SCAN
        Cache::flush(); // For now, clear all cache - can be optimized later
    }

    // Legacy methods for backward compatibility
    public function indexMessage(Message|array $message): void
    {
        // Clear cache when new messages are indexed
        if ($message instanceof Message) {
            $this->clearSearchCache($message->account);
        }
    }

    public function removeMessage(int $messageId): void
    {
        // Clear cache when messages are removed
        try {
            $message = Message::find($messageId);
            if ($message) {
                $this->clearSearchCache($message->account);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache for removed message', ['message_id' => $messageId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Legacy search method for backward compatibility.
     */
    public function search(string $q, array $options = []): array
    {
        $limit = $options['limit'] ?? 50;
        $accountId = $options['account_id'] ?? null;

        $query = Message::query();
        
        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        // Use optimized search if possible
        if ($this->useGinSearch()) {
            $tsquery = $this->prepareTsQuery($q);
            $query->whereRaw('to_tsvector(\'english\', content) @@ to_tsquery(?)', [$tsquery]);
        } else {
            $query->where('content', 'like', "%{$q}%");
        }

        return $query->limit($limit)->get()->toArray();
    }
}
