<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for various performance
    | optimizations including caching, database, and queue settings.
    |
    */

    'cache' => [
        /*
         * Configuration cache settings
         */
        'config' => [
            'enabled' => env('CONFIG_CACHE_ENABLED', true),
            'ttl' => env('CONFIG_CACHE_TTL', 7200), // 2 hours
            'warm_up_on_boot' => env('CONFIG_CACHE_WARM_UP', true),
            'preload_frequent' => env('CONFIG_CACHE_PRELOAD', true),
        ],

        /*
         * Query result caching
         */
        'query' => [
            'enabled' => env('QUERY_CACHE_ENABLED', true),
            'ttl' => env('QUERY_CACHE_TTL', 300), // 5 minutes
            'conversation_list_ttl' => env('CONVERSATION_CACHE_TTL', 180), // 3 minutes
            'message_cache_ttl' => env('MESSAGE_CACHE_TTL', 120), // 2 minutes
        ],

        /*
         * Model caching
         */
        'model' => [
            'enabled' => env('MODEL_CACHE_ENABLED', true),
            'ttl' => env('MODEL_CACHE_TTL', 600), // 10 minutes
            'relations_ttl' => env('MODEL_RELATIONS_CACHE_TTL', 300), // 5 minutes
        ],
    ],

    'database' => [
        /*
         * Connection optimization
         */
        'connections' => [
            'pool_size' => env('DB_POOL_SIZE', 10),
            'max_connections' => env('DB_MAX_CONNECTIONS', 100),
            'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 60),
            'idle_timeout' => env('DB_IDLE_TIMEOUT', 300),
        ],

        /*
         * Query optimization
         */
        'queries' => [
            'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // milliseconds
            'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
            'optimize_indexes' => env('OPTIMIZE_INDEXES', true),
            'analyze_tables' => env('ANALYZE_TABLES', true),
        ],

        /*
         * Read/Write splitting
         */
        'read_write_split' => [
            'enabled' => env('DB_READ_WRITE_SPLIT', false),
            'read_connections' => env('DB_READ_CONNECTIONS', 2),
            'write_connections' => env('DB_WRITE_CONNECTIONS', 1),
        ],
    ],

    'queue' => [
        /*
         * Queue optimization settings
         */
        'optimization' => [
            'enabled' => env('QUEUE_OPTIMIZATION_ENABLED', true),
            'auto_scaling' => env('QUEUE_AUTO_SCALING', true),
            'priority_routing' => env('QUEUE_PRIORITY_ROUTING', true),
            'batch_processing' => env('QUEUE_BATCH_PROCESSING', true),
        ],

        /*
         * Worker configuration
         */
        'workers' => [
            'critical' => env('QUEUE_WORKERS_CRITICAL', 8),
            'high' => env('QUEUE_WORKERS_HIGH', 6),
            'medium' => env('QUEUE_WORKERS_MEDIUM', 4),
            'low' => env('QUEUE_WORKERS_LOW', 2),
        ],

        /*
         * Queue timeouts
         */
        'timeouts' => [
            'critical' => env('QUEUE_TIMEOUT_CRITICAL', 30),
            'high' => env('QUEUE_TIMEOUT_HIGH', 60),
            'medium' => env('QUEUE_TIMEOUT_MEDIUM', 300),
            'low' => env('QUEUE_TIMEOUT_LOW', 900),
        ],

        /*
         * Retry configuration
         */
        'retries' => [
            'critical' => env('QUEUE_RETRIES_CRITICAL', 3),
            'high' => env('QUEUE_RETRIES_HIGH', 3),
            'medium' => env('QUEUE_RETRIES_MEDIUM', 2),
            'low' => env('QUEUE_RETRIES_LOW', 1),
        ],

        /*
         * Cleanup settings
         */
        'cleanup' => [
            'failed_jobs_retention' => env('FAILED_JOBS_RETENTION_DAYS', 7),
            'completed_jobs_retention' => env('COMPLETED_JOBS_RETENTION_HOURS', 24),
            'auto_cleanup' => env('QUEUE_AUTO_CLEANUP', true),
        ],
    ],

    'monitoring' => [
        /*
         * Performance monitoring
         */
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'metrics_retention' => env('METRICS_RETENTION_DAYS', 30),
        'alert_thresholds' => [
            'queue_size' => env('ALERT_QUEUE_SIZE', 1000),
            'failure_rate' => env('ALERT_FAILURE_RATE', 10), // percentage
            'response_time' => env('ALERT_RESPONSE_TIME', 2000), // milliseconds
            'memory_usage' => env('ALERT_MEMORY_USAGE', 80), // percentage
        ],
    ],

    'production' => [
        /*
         * Production-specific optimizations
         */
        'opcache' => [
            'enabled' => env('OPCACHE_ENABLED', true),
            'validate_timestamps' => env('OPCACHE_VALIDATE_TIMESTAMPS', false),
            'revalidate_freq' => env('OPCACHE_REVALIDATE_FREQ', 0),
            'max_accelerated_files' => env('OPCACHE_MAX_FILES', 20000),
            'memory_consumption' => env('OPCACHE_MEMORY', 256), // MB
        ],

        /*
         * Laravel optimizations
         */
        'laravel' => [
            'config_cache' => env('LARAVEL_CONFIG_CACHE', true),
            'route_cache' => env('LARAVEL_ROUTE_CACHE', true),
            'view_cache' => env('LARAVEL_VIEW_CACHE', true),
            'event_cache' => env('LARAVEL_EVENT_CACHE', true),
        ],

        /*
         * Asset optimization
         */
        'assets' => [
            'minification' => env('ASSET_MINIFICATION', true),
            'compression' => env('ASSET_COMPRESSION', true),
            'cdn_enabled' => env('CDN_ENABLED', false),
            'cache_busting' => env('ASSET_CACHE_BUSTING', true),
        ],
    ],

    'redis' => [
        /*
         * Redis optimization
         */
        'optimization' => [
            'serializer' => env('REDIS_SERIALIZER', 'igbinary'),
            'compression' => env('REDIS_COMPRESSION', 'lz4'),
            'persistent' => env('REDIS_PERSISTENT', true),
            'tcp_keepalive' => env('REDIS_TCP_KEEPALIVE', 1),
        ],

        /*
         * Connection pooling
         */
        'pool' => [
            'min_connections' => env('REDIS_POOL_MIN', 1),
            'max_connections' => env('REDIS_POOL_MAX', 10),
            'wait_timeout' => env('REDIS_POOL_WAIT_TIMEOUT', 3),
        ],
    ],

    'memory' => [
        /*
         * Memory optimization
         */
        'limits' => [
            'php_memory_limit' => env('PHP_MEMORY_LIMIT', '512M'),
            'queue_memory_limit' => env('QUEUE_MEMORY_LIMIT', '256M'),
            'cache_memory_limit' => env('CACHE_MEMORY_LIMIT', '128M'),
        ],

        /*
         * Garbage collection
         */
        'gc' => [
            'enabled' => env('GC_OPTIMIZATION', true),
            'probability' => env('GC_PROBABILITY', 1),
            'divisor' => env('GC_DIVISOR', 1000),
        ],
    ],

];