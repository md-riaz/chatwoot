<?php

namespace App\Actions\Search;

use App\Models\Account;
use App\Models\User;
use App\Repositories\Search\SearchRepository;
use App\Actions\Filter\ApplyPermissionFiltersAction;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class PerformSearchAction
{
    use AsAction;

    private SearchRepository $searchRepository;
    private ApplyPermissionFiltersAction $permissionFilterAction;
    private array $config;

    public function __construct()
    {
        $this->searchRepository = new SearchRepository();
        $this->permissionFilterAction = new ApplyPermissionFiltersAction();
        $this->config = config('search', [
            'default_per_page' => 15,
            'max_per_page' => 100,
            'time_window_months' => 3,
            'enable_gin_search' => true,
            'enable_advanced_search' => false,
            'cache_ttl' => 300, // 5 minutes
        ]);
    }

    /**
     * Perform comprehensive search across all types or specific type
     */
    public function handle(
        string $searchQuery,
        string $searchType,
        User $user,
        Account $account,
        array $params = []
    ): array {
        $cacheKey = $this->generateCacheKey($searchQuery, $searchType, $user->id, $account->id, $params);
        
        return Cache::remember($cacheKey, $this->config['cache_ttl'], function () use ($searchQuery, $searchType, $user, $account, $params) {
            return match ($searchType) {
                'Message' => ['messages' => $this->searchMessages($searchQuery, $user, $account, $params)],
                'Conversation' => ['conversations' => $this->searchConversations($searchQuery, $user, $account, $params)],
                'Contact' => ['contacts' => $this->searchContacts($searchQuery, $user, $account, $params)],
                'Article' => ['articles' => $this->searchArticles($searchQuery, $user, $account, $params)],
                default => [
                    'contacts' => $this->searchContacts($searchQuery, $user, $account, $params),
                    'messages' => $this->searchMessages($searchQuery, $user, $account, $params),
                    'conversations' => $this->searchConversations($searchQuery, $user, $account, $params),
                    'articles' => $this->searchArticles($searchQuery, $user, $account, $params),
                ]
            };
        });
    }

    /**
     * Search messages with optimized strategies
     */
    public function searchMessages(string $searchQuery, User $user, Account $account, array $params = [])
    {
        return $this->searchRepository->searchMessages($searchQuery, $user, $account, $params);
    }

    /**
     * Search conversations with optimized queries
     */
    public function searchConversations(string $searchQuery, User $user, Account $account, array $params = [])
    {
        return $this->searchRepository->searchConversations($searchQuery, $user, $account, $params);
    }

    /**
     * Search contacts with permission-based access
     */
    public function searchContacts(string $searchQuery, User $user, Account $account, array $params = [])
    {
        return $this->searchRepository->searchContacts($searchQuery, $user, $account, $params);
    }

    /**
     * Search articles with full-text search support
     */
    public function searchArticles(string $searchQuery, User $user, Account $account, array $params = [])
    {
        return $this->searchRepository->searchArticles($searchQuery, $user, $account, $params);
    }

    /**
     * Clear search cache for account
     */
    public function clearSearchCache(Account $account): void
    {
        $pattern = "search:{$account->id}:*";
        // Note: This is a simplified cache clearing. In production, you might want to use Redis SCAN
        Cache::flush(); // For now, clear all cache - can be optimized later
    }

    /**
     * Generate cache key for search results
     */
    private function generateCacheKey(string $searchQuery, string $searchType, int $userId, int $accountId, array $params): string
    {
        $paramsHash = md5(serialize($params));
        return "search:{$accountId}:{$userId}:{$searchType}:" . md5($searchQuery) . ":{$paramsHash}";
    }
}