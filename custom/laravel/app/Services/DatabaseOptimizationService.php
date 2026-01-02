<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Builder;

/**
 * Database Optimization Service
 * 
 * Provides database query optimization, connection pooling,
 * and performance monitoring for complex operations.
 */
class DatabaseOptimizationService
{
    private const CACHE_PREFIX = 'db_optimization:';
    private const QUERY_CACHE_TTL = 300; // 5 minutes
    private const SLOW_QUERY_THRESHOLD = 1000; // 1 second in milliseconds

    /**
     * Optimize database connections for high-load scenarios.
     */
    public static function optimizeConnections(): array
    {
        $results = [
            'optimizations_applied' => 0,
            'connection_pools_configured' => 0,
            'errors' => [],
        ];

        try {
            // Configure connection pooling settings
            $connections = config('database.connections');
            
            foreach ($connections as $name => $config) {
                if ($config['driver'] === 'mysql' || $config['driver'] === 'pgsql') {
                    // Apply optimized connection settings
                    $optimizedConfig = self::getOptimizedConnectionConfig($config);
                    config(["database.connections.{$name}" => $optimizedConfig]);
                    
                    $results['connection_pools_configured']++;
                }
            }

            // Set global database optimization settings
            DB::statement('SET SESSION query_cache_type = ON');
            DB::statement('SET SESSION query_cache_size = 67108864'); // 64MB
            
            $results['optimizations_applied']++;

            Log::info('Database connections optimized', $results);

        } catch (\Exception $e) {
            $results['errors'][] = 'Database optimization failed: ' . $e->getMessage();
            Log::error('Database optimization failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Get optimized connection configuration.
     */
    private static function getOptimizedConnectionConfig(array $config): array
    {
        $optimized = $config;

        // MySQL optimizations
        if ($config['driver'] === 'mysql') {
            $optimized['options'] = array_merge($config['options'] ?? [], [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 
                    "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
            ]);
        }

        // PostgreSQL optimizations
        if ($config['driver'] === 'pgsql') {
            $optimized['options'] = array_merge($config['options'] ?? [], [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $optimized;
    }

    /**
     * Optimize complex conversation queries.
     */
    public static function optimizeConversationQueries(): void
    {
        // Create optimized indexes for conversation queries
        $indexes = [
            'conversations_account_status_priority_idx' => [
                'table' => 'conversations',
                'columns' => ['account_id', 'status', 'priority', 'last_activity_at'],
            ],
            'conversations_assignee_status_idx' => [
                'table' => 'conversations',
                'columns' => ['assignee_id', 'status', 'last_activity_at'],
            ],
            'conversations_team_status_idx' => [
                'table' => 'conversations',
                'columns' => ['team_id', 'status', 'last_activity_at'],
            ],
            'conversations_inbox_status_priority_idx' => [
                'table' => 'conversations',
                'columns' => ['inbox_id', 'status', 'priority', 'created_at'],
            ],
        ];

        foreach ($indexes as $name => $index) {
            try {
                if (!self::indexExists($index['table'], $name)) {
                    $columns = implode(', ', $index['columns']);
                    DB::statement("CREATE INDEX {$name} ON {$index['table']} ({$columns})");
                    
                    Log::info("Created database index: {$name}");
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create index {$name}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Optimize message queries with proper indexing.
     */
    public static function optimizeMessageQueries(): void
    {
        $indexes = [
            'messages_conversation_created_idx' => [
                'table' => 'messages',
                'columns' => ['conversation_id', 'created_at', 'message_type'],
            ],
            'messages_sender_type_idx' => [
                'table' => 'messages',
                'columns' => ['sender_id', 'sender_type', 'created_at'],
            ],
            'messages_account_inbox_created_idx' => [
                'table' => 'messages',
                'columns' => ['account_id', 'inbox_id', 'created_at'],
            ],
            'messages_external_source_idx' => [
                'table' => 'messages',
                'columns' => ['external_source_id'],
            ],
        ];

        foreach ($indexes as $name => $index) {
            try {
                if (!self::indexExists($index['table'], $name)) {
                    $columns = implode(', ', $index['columns']);
                    DB::statement("CREATE INDEX {$name} ON {$index['table']} ({$columns})");
                    
                    Log::info("Created database index: {$name}");
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create index {$name}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Optimize reporting queries with aggregation indexes.
     */
    public static function optimizeReportingQueries(): void
    {
        $indexes = [
            'reporting_events_account_name_created_idx' => [
                'table' => 'reporting_events',
                'columns' => ['account_id', 'name', 'created_at'],
            ],
            'reporting_events_conversation_name_idx' => [
                'table' => 'reporting_events',
                'columns' => ['conversation_id', 'name', 'event_start_time'],
            ],
            'reporting_events_user_name_created_idx' => [
                'table' => 'reporting_events',
                'columns' => ['user_id', 'name', 'created_at'],
            ],
            'reporting_events_inbox_name_created_idx' => [
                'table' => 'reporting_events',
                'columns' => ['inbox_id', 'name', 'created_at'],
            ],
        ];

        foreach ($indexes as $name => $index) {
            try {
                if (!self::indexExists($index['table'], $name)) {
                    $columns = implode(', ', $index['columns']);
                    DB::statement("CREATE INDEX {$name} ON {$index['table']} ({$columns})");
                    
                    Log::info("Created database index: {$name}");
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create index {$name}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Check if index exists on table.
     */
    private static function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
                return !empty($result);
            } elseif ($driver === 'pgsql') {
                $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
                return !empty($result);
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create cached query builder for frequently used queries.
     */
    public static function getCachedQuery(string $key, \Closure $queryBuilder, int $ttl = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . 'query:' . $key;
        $ttl = $ttl ?? self::QUERY_CACHE_TTL;

        return Cache::remember($cacheKey, $ttl, function () use ($queryBuilder) {
            $startTime = microtime(true);
            $result = $queryBuilder();
            $executionTime = (microtime(true) - $startTime) * 1000;

            // Log slow queries
            if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
                Log::warning('Slow query detected', [
                    'execution_time' => $executionTime,
                    'cache_key' => $cacheKey ?? 'unknown',
                ]);
            }

            return $result;
        });
    }

    /**
     * Optimize conversation list queries with pagination.
     */
    public static function getOptimizedConversationList(array $filters, int $perPage = 25): mixed
    {
        $cacheKey = 'conversations:' . md5(serialize($filters)) . ':page:' . ($filters['page'] ?? 1);

        return self::getCachedQuery($cacheKey, function () use ($filters, $perPage) {
            $query = DB::table('conversations')
                ->select([
                    'conversations.*',
                    'contacts.name as contact_name',
                    'contacts.email as contact_email',
                    'inboxes.name as inbox_name',
                    'users.name as assignee_name',
                ])
                ->leftJoin('contacts', 'conversations.contact_id', '=', 'contacts.id')
                ->leftJoin('inboxes', 'conversations.inbox_id', '=', 'inboxes.id')
                ->leftJoin('users', 'conversations.assignee_id', '=', 'users.id')
                ->where('conversations.account_id', $filters['account_id']);

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('conversations.status', $filters['status']);
            }

            if (isset($filters['inbox_id'])) {
                $query->where('conversations.inbox_id', $filters['inbox_id']);
            }

            if (isset($filters['assignee_id'])) {
                $query->where('conversations.assignee_id', $filters['assignee_id']);
            }

            if (isset($filters['team_id'])) {
                $query->where('conversations.team_id', $filters['team_id']);
            }

            // Optimize ordering
            $query->orderBy('conversations.last_activity_at', 'desc')
                  ->orderBy('conversations.priority', 'desc');

            return $query->paginate($perPage);
        }, 180); // Cache for 3 minutes
    }

    /**
     * Optimize message queries for conversation view.
     */
    public static function getOptimizedConversationMessages(int $conversationId, int $limit = 50): mixed
    {
        $cacheKey = "conversation_messages:{$conversationId}:limit:{$limit}";

        return self::getCachedQuery($cacheKey, function () use ($conversationId, $limit) {
            return DB::table('messages')
                ->select([
                    'messages.*',
                    'users.name as sender_name',
                    'users.email as sender_email',
                    'contacts.name as contact_name',
                    'contacts.email as contact_email',
                ])
                ->leftJoin('users', function ($join) {
                    $join->on('messages.sender_id', '=', 'users.id')
                         ->where('messages.sender_type', '=', 'User');
                })
                ->leftJoin('contacts', function ($join) {
                    $join->on('messages.sender_id', '=', 'contacts.id')
                         ->where('messages.sender_type', '=', 'Contact');
                })
                ->where('messages.conversation_id', $conversationId)
                ->orderBy('messages.created_at', 'asc')
                ->limit($limit)
                ->get();
        }, 120); // Cache for 2 minutes
    }

    /**
     * Get database performance metrics.
     */
    public static function getPerformanceMetrics(): array
    {
        $metrics = [
            'connection_count' => 0,
            'slow_queries' => 0,
            'cache_hit_ratio' => 0,
            'index_usage' => [],
            'table_sizes' => [],
        ];

        try {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                // Get connection count
                $result = DB::select('SHOW STATUS LIKE "Threads_connected"');
                $metrics['connection_count'] = $result[0]->Value ?? 0;

                // Get slow query count
                $result = DB::select('SHOW STATUS LIKE "Slow_queries"');
                $metrics['slow_queries'] = $result[0]->Value ?? 0;

                // Get table sizes
                $tables = ['conversations', 'messages', 'contacts', 'reporting_events'];
                foreach ($tables as $table) {
                    $result = DB::select("
                        SELECT 
                            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                        FROM information_schema.TABLES 
                        WHERE table_schema = DATABASE() AND table_name = ?
                    ", [$table]);
                    
                    $metrics['table_sizes'][$table] = $result[0]->size_mb ?? 0;
                }
            }

        } catch (\Exception $e) {
            Log::warning('Failed to get database performance metrics', [
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }

    /**
     * Clear query cache.
     */
    public static function clearQueryCache(string $pattern = null): void
    {
        if ($pattern) {
            $cacheKey = self::CACHE_PREFIX . 'query:' . $pattern;
            Cache::forget($cacheKey);
        } else {
            // Clear all query cache
            Cache::flush();
        }
    }

    /**
     * Analyze and suggest query optimizations.
     */
    public static function analyzeQueryPerformance(): array
    {
        $analysis = [
            'suggestions' => [],
            'missing_indexes' => [],
            'slow_queries' => [],
        ];

        try {
            // Analyze frequently used queries
            $slowQueries = [
                'conversations_without_assignee_index' => "
                    SELECT COUNT(*) FROM conversations 
                    WHERE assignee_id IS NULL AND status = 0
                ",
                'messages_without_conversation_index' => "
                    SELECT COUNT(*) FROM messages m
                    LEFT JOIN conversations c ON m.conversation_id = c.id
                    WHERE c.id IS NULL
                ",
                'reporting_events_without_date_index' => "
                    SELECT COUNT(*) FROM reporting_events
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ",
            ];

            foreach ($slowQueries as $name => $query) {
                $startTime = microtime(true);
                DB::select($query);
                $executionTime = (microtime(true) - $startTime) * 1000;

                if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
                    $analysis['slow_queries'][] = [
                        'name' => $name,
                        'execution_time' => $executionTime,
                        'query' => $query,
                    ];
                }
            }

            // Suggest missing indexes
            $analysis['missing_indexes'] = [
                'conversations_assignee_null_status' => [
                    'table' => 'conversations',
                    'columns' => ['assignee_id', 'status'],
                    'reason' => 'Optimize unassigned conversation queries',
                ],
                'messages_conversation_type' => [
                    'table' => 'messages',
                    'columns' => ['conversation_id', 'message_type'],
                    'reason' => 'Optimize message type filtering',
                ],
                'reporting_events_date_range' => [
                    'table' => 'reporting_events',
                    'columns' => ['created_at', 'account_id'],
                    'reason' => 'Optimize date range reporting queries',
                ],
            ];

            // General suggestions
            $analysis['suggestions'] = [
                'Enable query cache for frequently accessed data',
                'Consider partitioning large tables by date',
                'Implement read replicas for reporting queries',
                'Use connection pooling for high-concurrency scenarios',
                'Regular ANALYZE TABLE maintenance for MySQL',
                'Consider materialized views for complex reporting queries',
            ];

        } catch (\Exception $e) {
            Log::error('Query performance analysis failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $analysis;
    }
}