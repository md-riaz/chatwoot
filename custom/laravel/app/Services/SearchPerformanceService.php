<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing search performance optimizations and monitoring.
 */
class SearchPerformanceService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('search', []);
    }

    /**
     * Check if full-text search indexes are available and working.
     */
    public function checkSearchIndexes(): array
    {
        $results = [
            'database_driver' => DB::getDriverName(),
            'indexes' => [],
            'recommendations' => [],
        ];

        try {
            if (DB::getDriverName() === 'pgsql') {
                $results['indexes'] = $this->checkPostgreSQLIndexes();
            } elseif (DB::getDriverName() === 'mysql') {
                $results['indexes'] = $this->checkMySQLIndexes();
            }

            $results['recommendations'] = $this->generateRecommendations($results['indexes']);
        } catch (\Exception $e) {
            Log::error('Failed to check search indexes', ['error' => $e->getMessage()]);
            $results['error'] = 'Failed to check search indexes: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Check PostgreSQL GIN indexes.
     */
    private function checkPostgreSQLIndexes(): array
    {
        $indexes = [];

        // Check messages content GIN index
        $messagesIndex = DB::select("
            SELECT schemaname, tablename, indexname, indexdef 
            FROM pg_indexes 
            WHERE tablename = 'messages' 
            AND indexname LIKE '%content%gin%'
        ");

        $indexes['messages_content_gin'] = [
            'exists' => !empty($messagesIndex),
            'details' => $messagesIndex,
            'performance_impact' => 'High - Enables fast full-text search on message content',
        ];

        // Check contacts search GIN index
        $contactsIndex = DB::select("
            SELECT schemaname, tablename, indexname, indexdef 
            FROM pg_indexes 
            WHERE tablename = 'contacts' 
            AND indexname LIKE '%search%gin%'
        ");

        $indexes['contacts_search_gin'] = [
            'exists' => !empty($contactsIndex),
            'details' => $contactsIndex,
            'performance_impact' => 'Medium - Enables fast full-text search on contact fields',
        ];

        // Check articles content GIN index (if articles table exists)
        try {
            $articlesIndex = DB::select("
                SELECT schemaname, tablename, indexname, indexdef 
                FROM pg_indexes 
                WHERE tablename = 'articles' 
                AND indexname LIKE '%content%gin%'
            ");

            $indexes['articles_content_gin'] = [
                'exists' => !empty($articlesIndex),
                'details' => $articlesIndex,
                'performance_impact' => 'Medium - Enables fast full-text search on articles',
            ];
        } catch (\Exception $e) {
            $indexes['articles_content_gin'] = [
                'exists' => false,
                'error' => 'Articles table may not exist',
            ];
        }

        return $indexes;
    }

    /**
     * Check MySQL full-text indexes.
     */
    private function checkMySQLIndexes(): array
    {
        $indexes = [];

        // Check messages full-text index
        $messagesIndex = DB::select("
            SHOW INDEX FROM messages 
            WHERE Index_type = 'FULLTEXT' 
            AND Column_name = 'content'
        ");

        $indexes['messages_content_fulltext'] = [
            'exists' => !empty($messagesIndex),
            'details' => $messagesIndex,
            'performance_impact' => 'High - Enables fast full-text search on message content',
        ];

        // Check contacts full-text index
        $contactsIndex = DB::select("
            SHOW INDEX FROM contacts 
            WHERE Index_type = 'FULLTEXT'
        ");

        $indexes['contacts_search_fulltext'] = [
            'exists' => !empty($contactsIndex),
            'details' => $contactsIndex,
            'performance_impact' => 'Medium - Enables fast full-text search on contact fields',
        ];

        return $indexes;
    }

    /**
     * Generate performance recommendations based on index status.
     */
    private function generateRecommendations(array $indexes): array
    {
        $recommendations = [];

        foreach ($indexes as $indexName => $indexInfo) {
            if (!$indexInfo['exists']) {
                $recommendations[] = [
                    'type' => 'missing_index',
                    'index' => $indexName,
                    'priority' => $this->getIndexPriority($indexName),
                    'action' => "Run migrations to create {$indexName} for better search performance",
                    'impact' => $indexInfo['performance_impact'] ?? 'Unknown',
                ];
            }
        }

        // Add general recommendations
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'optimization',
                'priority' => 'low',
                'action' => 'All search indexes are properly configured',
                'impact' => 'Search performance should be optimal',
            ];
        }

        return $recommendations;
    }

    /**
     * Get priority level for index recommendations.
     */
    private function getIndexPriority(string $indexName): string
    {
        return match (true) {
            str_contains($indexName, 'messages_content') => 'high',
            str_contains($indexName, 'contacts_search') => 'medium',
            str_contains($indexName, 'articles_content') => 'medium',
            default => 'low',
        };
    }

    /**
     * Benchmark search performance for different query types.
     */
    public function benchmarkSearchPerformance(string $sampleQuery = 'test'): array
    {
        $benchmarks = [];

        try {
            // Benchmark message search
            $benchmarks['message_search'] = $this->benchmarkQuery(
                'messages',
                "SELECT COUNT(*) FROM messages WHERE content ILIKE '%{$sampleQuery}%'",
                'LIKE search on messages'
            );

            // Benchmark full-text search if available
            if (DB::getDriverName() === 'pgsql') {
                $tsquery = str_replace(' ', ' <-> ', $sampleQuery);
                $benchmarks['message_fulltext'] = $this->benchmarkQuery(
                    'messages',
                    "SELECT COUNT(*) FROM messages WHERE to_tsvector('english', content) @@ to_tsquery('{$tsquery}')",
                    'Full-text search on messages'
                );
            }

            // Benchmark conversation search
            $benchmarks['conversation_search'] = $this->benchmarkQuery(
                'conversations',
                "SELECT COUNT(*) FROM conversations c JOIN contacts ct ON c.contact_id = ct.id WHERE CAST(c.display_id AS TEXT) ILIKE '%{$sampleQuery}%' OR ct.name ILIKE '%{$sampleQuery}%'",
                'JOIN search on conversations'
            );

        } catch (\Exception $e) {
            Log::error('Search benchmark failed', ['error' => $e->getMessage()]);
            $benchmarks['error'] = 'Benchmark failed: ' . $e->getMessage();
        }

        return $benchmarks;
    }

    /**
     * Benchmark a specific query.
     */
    private function benchmarkQuery(string $table, string $query, string $description): array
    {
        $startTime = microtime(true);
        
        try {
            $result = DB::select($query);
            $endTime = microtime(true);
            
            return [
                'description' => $description,
                'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                'result_count' => $result[0]->count ?? 0,
                'status' => 'success',
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            
            return [
                'description' => $description,
                'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get search performance statistics.
     */
    public function getSearchStatistics(): array
    {
        $cacheKey = 'search_performance_stats';
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                $stats = [
                    'database_driver' => DB::getDriverName(),
                    'table_sizes' => $this->getTableSizes(),
                    'index_usage' => $this->getIndexUsage(),
                    'search_config' => $this->config,
                ];

                return $stats;
            } catch (\Exception $e) {
                Log::error('Failed to get search statistics', ['error' => $e->getMessage()]);
                return ['error' => 'Failed to get search statistics: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Get table sizes for search-related tables.
     */
    private function getTableSizes(): array
    {
        $sizes = [];
        $tables = ['messages', 'conversations', 'contacts', 'articles'];

        foreach ($tables as $table) {
            try {
                if (DB::getDriverName() === 'pgsql') {
                    $result = DB::select("
                        SELECT 
                            pg_size_pretty(pg_total_relation_size('{$table}')) as size,
                            pg_total_relation_size('{$table}') as size_bytes
                    ");
                    $sizes[$table] = $result[0] ?? null;
                } else {
                    $result = DB::select("
                        SELECT 
                            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                            (data_length + index_length) as size_bytes
                        FROM information_schema.tables 
                        WHERE table_name = '{$table}'
                    ");
                    $sizes[$table] = $result[0] ?? null;
                }
            } catch (\Exception $e) {
                $sizes[$table] = ['error' => $e->getMessage()];
            }
        }

        return $sizes;
    }

    /**
     * Get index usage statistics (PostgreSQL only).
     */
    private function getIndexUsage(): array
    {
        if (DB::getDriverName() !== 'pgsql') {
            return ['note' => 'Index usage statistics only available for PostgreSQL'];
        }

        try {
            $indexUsage = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    indexname,
                    idx_tup_read,
                    idx_tup_fetch
                FROM pg_stat_user_indexes 
                WHERE tablename IN ('messages', 'conversations', 'contacts', 'articles')
                AND indexname LIKE '%search%' OR indexname LIKE '%gin%' OR indexname LIKE '%fulltext%'
                ORDER BY idx_tup_read DESC
            ");

            return $indexUsage;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Clear search performance cache.
     */
    public function clearPerformanceCache(): void
    {
        Cache::forget('search_performance_stats');
    }
}