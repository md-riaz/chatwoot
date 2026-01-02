<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Account;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Search across conversations, contacts, messages, and articles.
     */
    public function index(Account $account, SearchRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = $request->getQuery();
            $type = $request->getType();
            
            $params = [
                'limit' => $request->getPerPage(),
                'sort_by' => $request->getSortBy(),
                'sort_order' => $request->getSortOrder(),
            ];

            $results = $this->searchService->perform($query, $type, $user, $account, $params);

            return response()->json([
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'type' => $type,
                    'total_types' => count($results),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Search failed', [
                'account_id' => $account->id,
                'user_id' => $request->user()->id,
                'query' => $request->getQuery(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Search failed. Please try again.',
                'data' => []
            ], 500);
        }
    }

    /**
     * Search conversations with enhanced performance.
     */
    public function conversations(Account $account, SearchRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = $request->getQuery();
            
            $params = [
                'limit' => $request->getPerPage(),
                'sort_by' => $request->getSortBy(),
                'sort_order' => $request->getSortOrder(),
            ];

            $conversations = $this->searchService->filterConversations($query, $user, $account, $params);

            // Convert to paginated response format
            $paginatedData = [
                'data' => $conversations->values(),
                'current_page' => $request->getPage(),
                'per_page' => $request->getPerPage(),
                'total' => $conversations->count(),
                'last_page' => 1, // Since we're limiting results, this is simplified
            ];

            return response()->json($paginatedData);
        } catch (\Exception $e) {
            Log::error('Conversation search failed', [
                'account_id' => $account->id,
                'user_id' => $request->user()->id,
                'query' => $request->getQuery(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Conversation search failed. Please try again.',
                'data' => []
            ], 500);
        }
    }

    /**
     * Search contacts with permission filtering.
     */
    public function contacts(Account $account, SearchRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = $request->getQuery();
            
            $params = [
                'limit' => $request->getPerPage(),
                'sort_by' => $request->getSortBy(),
                'sort_order' => $request->getSortOrder(),
            ];

            $contacts = $this->searchService->filterContacts($query, $user, $account, $params);

            // Convert to paginated response format
            $paginatedData = [
                'data' => $contacts->values(),
                'current_page' => $request->getPage(),
                'per_page' => $request->getPerPage(),
                'total' => $contacts->count(),
                'last_page' => 1, // Since we're limiting results, this is simplified
            ];

            return response()->json($paginatedData);
        } catch (\Exception $e) {
            Log::error('Contact search failed', [
                'account_id' => $account->id,
                'user_id' => $request->user()->id,
                'query' => $request->getQuery(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Contact search failed. Please try again.',
                'data' => []
            ], 500);
        }
    }

    /**
     * Search messages with full-text search optimization.
     */
    public function messages(Account $account, SearchRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = $request->getQuery();
            
            $params = [
                'limit' => $request->getPerPage(),
                'sort_by' => $request->getSortBy(),
                'sort_order' => $request->getSortOrder(),
            ];

            $messages = $this->searchService->filterMessages($query, $user, $account, $params);

            // Convert to paginated response format
            $paginatedData = [
                'data' => $messages->values(),
                'current_page' => $request->getPage(),
                'per_page' => $request->getPerPage(),
                'total' => $messages->count(),
                'last_page' => 1, // Since we're limiting results, this is simplified
            ];

            return response()->json($paginatedData);
        } catch (\Exception $e) {
            Log::error('Message search failed', [
                'account_id' => $account->id,
                'user_id' => $request->user()->id,
                'query' => $request->getQuery(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Message search failed. Please try again.',
                'data' => []
            ], 500);
        }
    }

    /**
     * Search articles with full-text search support.
     */
    public function articles(Account $account, SearchRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = $request->getQuery();
            
            $params = [
                'limit' => $request->getPerPage(),
                'sort_by' => $request->getSortBy(),
                'sort_order' => $request->getSortOrder(),
            ];

            $articles = $this->searchService->filterArticles($query, $user, $account, $params);

            // Convert to paginated response format
            $paginatedData = [
                'data' => $articles->values(),
                'current_page' => $request->getPage(),
                'per_page' => $request->getPerPage(),
                'total' => $articles->count(),
                'last_page' => 1, // Since we're limiting results, this is simplified
            ];

            return response()->json($paginatedData);
        } catch (\Exception $e) {
            Log::error('Article search failed', [
                'account_id' => $account->id,
                'user_id' => $request->user()->id,
                'query' => $request->getQuery(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Article search failed. Please try again.',
                'data' => []
            ], 500);
        }
    }
}
