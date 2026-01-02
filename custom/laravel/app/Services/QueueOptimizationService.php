<?php

namespace App\Services;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MetricsRepository;

/**
 * Queue Optimization Service
 * 
 * Provides background job performance optimization, queue monitoring,
 * and intelligent job prioritization for high-throughput scenarios.
 */
class QueueOptimizationService
{
    private const CACHE_PREFIX = 'queue_optimization:';
    private const METRICS_TTL = 3600; // 1 hour
    private const HIGH_PRIORITY_THRESHOLD = 100; // Jobs per minute

    /**
     * Job priority mappings for intelligent queue management.
     */
    private const JOB_PRIORITIES = [
        // Critical real-time jobs
        'App\Jobs\Channels\ProcessIncomingMessageJob' => 'critical',
        'App\Jobs\Channels\SendOutgoingMessageJob' => 'critical',
        'App\Jobs\Webhooks\ProcessWebhookJob' => 'critical',
        
        // High priority jobs
        'App\Jobs\Notification\SendNotificationJob' => 'high',
        'App\Jobs\Conversation\UpdateConversationStatusJob' => 'high',
        'App\Jobs\Message\ProcessMessageJob' => 'high',
        
        // Medium priority jobs
        'App\Jobs\Integrations\SyncIntegrationDataJob' => 'medium',
        'App\Jobs\Reports\GenerateReportJob' => 'medium',
        'App\Jobs\Campaigns\ProcessCampaignJob' => 'medium',
        
        // Low priority jobs
        'App\Jobs\ImportContactsJob' => 'low',
        'App\Jobs\ExportContactsJob' => 'low',
        'App\Jobs\DeleteObjectJob' => 'low',
    ];

    /**
     * Queue configurations optimized for different job types.
     */
    private const OPTIMIZED_QUEUE_CONFIGS = [
        'critical' => [
            'connection' => 'redis',
            'queue' => 'critical',
            'timeout' => 30,
            'retry_after' => 60,
            'max_tries' => 3,
            'workers' => 8,
        ],
        'high' => [
            'connection' => 'redis',
            'queue' => 'high',
            'timeout' => 60,
            'retry_after' => 120,
            'max_tries' => 3,
            'workers' => 6,
        ],
        'medium' => [
            'connection' => 'redis',
            'queue' => 'default',
            'timeout' => 300,
            'retry_after' => 600,
            'max_tries' => 2,
            'workers' => 4,
        ],
        'low' => [
            'connection' => 'redis',
            'queue' => 'low',
            'timeout' => 900,
            'retry_after' => 1800,
            'max_tries' => 1,
            'workers' => 2,
        ],
    ];

    /**
     * Optimize queue configurations for performance.
     */
    public static function optimizeQueueConfiguration(): array
    {
        $results = [
            'queues_optimized' => 0,
            'workers_configured' => 0,
            'redis_optimized' => false,
            'errors' => [],
        ];

        try {
            // Configure Redis for optimal queue performance
            $redisConfig = self::getOptimizedRedisConfig();
            config(['database.redis.options' => array_merge(
                config('database.redis.options', []),
                $redisConfig
            )]);
            
            $results['redis_optimized'] = true;

            // Configure queue connections
            foreach (self::OPTIMIZED_QUEUE_CONFIGS as $priority => $config) {
                $connectionName = "redis_{$priority}";
                
                config(["queue.connections.{$connectionName}" => [
                    'driver' => 'redis',
                    'connection' => 'default',
                    'queue' => $config['queue'],
                    'retry_after' => $config['retry_after'],
                    'block_for' => null,
                    'after_commit' => false,
                ]]);
                
                $results['queues_optimized']++;
            }

            // Configure Horizon for optimal worker management
            self::configureHorizonOptimization();
            $results['workers_configured'] = array_sum(array_column(self::OPTIMIZED_QUEUE_CONFIGS, 'workers'));

            Log::info('Queue configuration optimized', $results);

        } catch (\Exception $e) {
            $results['errors'][] = 'Queue optimization failed: ' . $e->getMessage();
            Log::error('Queue optimization failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Get optimized Redis configuration for queues.
     */
    private static function getOptimizedRedisConfig(): array
    {
        return [
            'serializer' => 'igbinary', // More efficient than PHP serializer
            'compression' => 'lz4', // Fast compression
            'read_timeout' => 60,
            'tcp_keepalive' => 1,
            'retry_interval' => 100,
            'lazy_connect' => true,
        ];
    }

    /**
     * Configure Horizon for optimal performance.
     */
    private static function configureHorizonOptimization(): void
    {
        $horizonConfig = [
            'prefix' => env('HORIZON_PREFIX', 'horizon:'),
            'use' => 'default',
            'waits_for' => 60,
            'memory_limit' => 512,
            'trim' => [
                'recent' => 60,
                'pending' => 60,
                'completed' => 60,
                'failed' => 10080, // 1 week
            ],
            'environments' => [
                'production' => [
                    'supervisor-critical' => [
                        'connection' => 'redis',
                        'queue' => ['critical'],
                        'balance' => 'auto',
                        'processes' => 8,
                        'tries' => 3,
                        'nice' => -10, // Higher OS priority
                        'timeout' => 30,
                        'memory' => 256,
                    ],
                    'supervisor-high' => [
                        'connection' => 'redis',
                        'queue' => ['high'],
                        'balance' => 'auto',
                        'processes' => 6,
                        'tries' => 3,
                        'nice' => -5,
                        'timeout' => 60,
                        'memory' => 256,
                    ],
                    'supervisor-default' => [
                        'connection' => 'redis',
                        'queue' => ['default'],
                        'balance' => 'auto',
                        'processes' => 4,
                        'tries' => 2,
                        'nice' => 0,
                        'timeout' => 300,
                        'memory' => 256,
                    ],
                    'supervisor-low' => [
                        'connection' => 'redis',
                        'queue' => ['low'],
                        'balance' => 'simple',
                        'processes' => 2,
                        'tries' => 1,
                        'nice' => 10, // Lower OS priority
                        'timeout' => 900,
                        'memory' => 256,
                    ],
                ],
            ],
        ];

        config(['horizon' => array_merge(config('horizon', []), $horizonConfig)]);
    }

    /**
     * Get job priority based on job class.
     */
    public static function getJobPriority(string $jobClass): string
    {
        return self::JOB_PRIORITIES[$jobClass] ?? 'medium';
    }

    /**
     * Dispatch job to appropriate priority queue.
     */
    public static function dispatchOptimized($job): void
    {
        $jobClass = get_class($job);
        $priority = self::getJobPriority($jobClass);
        $config = self::OPTIMIZED_QUEUE_CONFIGS[$priority];

        // Set job properties based on priority
        if (method_exists($job, 'onQueue')) {
            $job->onQueue($config['queue']);
        }

        if (method_exists($job, 'onConnection')) {
            $job->onConnection($config['connection']);
        }

        if (method_exists($job, 'tries')) {
            $job->tries = $config['max_tries'];
        }

        if (method_exists($job, 'timeout')) {
            $job->timeout = $config['timeout'];
        }

        dispatch($job);
    }

    /**
     * Monitor queue performance and health.
     */
    public static function getQueueMetrics(): array
    {
        $metrics = [
            'queue_sizes' => [],
            'processing_times' => [],
            'failure_rates' => [],
            'worker_utilization' => [],
            'memory_usage' => [],
        ];

        try {
            // Get queue sizes
            foreach (array_keys(self::OPTIMIZED_QUEUE_CONFIGS) as $priority) {
                $queueName = self::OPTIMIZED_QUEUE_CONFIGS[$priority]['queue'];
                
                try {
                    if (config('queue.default') === 'redis') {
                        $size = Redis::llen("queues:{$queueName}");
                    } else {
                        // For database queues, count jobs in database
                        $size = DB::table('jobs')->where('queue', $queueName)->count();
                    }
                    $metrics['queue_sizes'][$queueName] = $size;
                } catch (\Exception $e) {
                    $metrics['queue_sizes'][$queueName] = 0;
                }
            }

            // Get processing times from Horizon if available
            if (class_exists(JobRepository::class)) {
                $jobRepo = app(JobRepository::class);
                $recentJobs = $jobRepo->getRecent();
                
                foreach ($recentJobs as $job) {
                    $queue = $job->queue ?? 'default';
                    if (!isset($metrics['processing_times'][$queue])) {
                        $metrics['processing_times'][$queue] = [];
                    }
                    
                    if ($job->completed_at && $job->started_at) {
                        $processingTime = strtotime($job->completed_at) - strtotime($job->started_at);
                        $metrics['processing_times'][$queue][] = $processingTime;
                    }
                }
                
                // Calculate averages
                foreach ($metrics['processing_times'] as $queue => $times) {
                    $metrics['processing_times'][$queue] = !empty($times) ? array_sum($times) / count($times) : 0;
                }
            }

            // Get failure rates
            foreach (array_keys(self::OPTIMIZED_QUEUE_CONFIGS) as $priority) {
                $queueName = self::OPTIMIZED_QUEUE_CONFIGS[$priority]['queue'];
                
                try {
                    if (config('queue.default') === 'redis') {
                        $failed = Redis::llen("queues:{$queueName}:failed");
                        $processed = Redis::get("queues:{$queueName}:processed") ?? 0;
                    } else {
                        // For database queues, count failed jobs
                        $failed = DB::table('failed_jobs')->where('queue', $queueName)->count();
                        $processed = 100; // Estimate for calculation
                    }
                    
                    $total = $failed + $processed;
                    $metrics['failure_rates'][$queueName] = $total > 0 ? ($failed / $total) * 100 : 0;
                } catch (\Exception $e) {
                    $metrics['failure_rates'][$queueName] = 0;
                }
            }

        } catch (\Exception $e) {
            Log::warning('Failed to get queue metrics', [
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }

    /**
     * Optimize job batching for bulk operations.
     */
    public static function createOptimizedBatch(array $jobs, string $name = null): \Illuminate\Bus\Batch
    {
        $batchSize = self::calculateOptimalBatchSize(count($jobs));
        $chunks = array_chunk($jobs, $batchSize);
        
        $batch = \Illuminate\Support\Facades\Bus::batch([])
            ->name($name ?? 'Optimized Batch')
            ->allowFailures()
            ->onQueue('default');

        foreach ($chunks as $chunk) {
            foreach ($chunk as $job) {
                $batch->add($job);
            }
        }

        return $batch->dispatch();
    }

    /**
     * Calculate optimal batch size based on job count and system resources.
     */
    private static function calculateOptimalBatchSize(int $jobCount): int
    {
        // Base batch size on available memory and job complexity
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = self::convertToBytes($memoryLimit);
        
        // Estimate memory per job (conservative estimate)
        $memoryPerJob = 1024 * 1024; // 1MB per job
        
        $maxBatchSize = min(
            floor($memoryLimitBytes / $memoryPerJob / 4), // Use 1/4 of available memory
            100 // Maximum batch size
        );
        
        return max(min($jobCount, $maxBatchSize), 1);
    }

    /**
     * Convert memory limit string to bytes.
     */
    private static function convertToBytes(string $memoryLimit): int
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $memoryLimit;
        }
    }

    /**
     * Auto-scale workers based on queue load.
     */
    public static function autoScaleWorkers(): array
    {
        $results = [
            'scaling_actions' => [],
            'current_load' => [],
            'recommendations' => [],
        ];

        try {
            $metrics = self::getQueueMetrics();
            
            foreach (self::OPTIMIZED_QUEUE_CONFIGS as $priority => $config) {
                $queueName = $config['queue'];
                $currentSize = $metrics['queue_sizes'][$queueName] ?? 0;
                $currentWorkers = $config['workers'];
                
                $results['current_load'][$queueName] = [
                    'queue_size' => $currentSize,
                    'workers' => $currentWorkers,
                    'load_per_worker' => $currentWorkers > 0 ? $currentSize / $currentWorkers : 0,
                ];
                
                // Recommend scaling based on queue size
                if ($currentSize > self::HIGH_PRIORITY_THRESHOLD) {
                    $recommendedWorkers = min(ceil($currentSize / 50), $currentWorkers * 2);
                    $results['recommendations'][$queueName] = [
                        'action' => 'scale_up',
                        'current_workers' => $currentWorkers,
                        'recommended_workers' => $recommendedWorkers,
                        'reason' => 'High queue load detected',
                    ];
                } elseif ($currentSize < 10 && $currentWorkers > 1) {
                    $recommendedWorkers = max(1, ceil($currentWorkers / 2));
                    $results['recommendations'][$queueName] = [
                        'action' => 'scale_down',
                        'current_workers' => $currentWorkers,
                        'recommended_workers' => $recommendedWorkers,
                        'reason' => 'Low queue load detected',
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Auto-scaling analysis failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Clear failed jobs and optimize queue cleanup.
     */
    public static function optimizeQueueCleanup(): array
    {
        $results = [
            'failed_jobs_cleared' => 0,
            'completed_jobs_trimmed' => 0,
            'memory_freed' => 0,
        ];

        try {
            // Clear old failed jobs (older than 7 days)
            $cutoffTime = now()->subDays(7)->timestamp;
            
            foreach (array_keys(self::OPTIMIZED_QUEUE_CONFIGS) as $priority) {
                $queueName = self::OPTIMIZED_QUEUE_CONFIGS[$priority]['queue'];
                
                // Clear old failed jobs
                $failedJobs = Redis::lrange("queues:{$queueName}:failed", 0, -1);
                $clearedCount = 0;
                
                foreach ($failedJobs as $index => $job) {
                    $jobData = json_decode($job, true);
                    if (isset($jobData['failed_at']) && $jobData['failed_at'] < $cutoffTime) {
                        Redis::lrem("queues:{$queueName}:failed", 1, $job);
                        $clearedCount++;
                    }
                }
                
                $results['failed_jobs_cleared'] += $clearedCount;
            }

            // Trim completed job history
            if (class_exists(JobRepository::class)) {
                $jobRepo = app(JobRepository::class);
                $trimmed = $jobRepo->trimRecentJobs();
                $results['completed_jobs_trimmed'] = $trimmed;
            }

            Log::info('Queue cleanup completed', $results);

        } catch (\Exception $e) {
            Log::error('Queue cleanup failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Get queue health status.
     */
    public static function getQueueHealthStatus(): array
    {
        $status = [
            'overall_health' => 'healthy',
            'queue_status' => [],
            'alerts' => [],
            'recommendations' => [],
        ];

        try {
            $metrics = self::getQueueMetrics();
            
            foreach (self::OPTIMIZED_QUEUE_CONFIGS as $priority => $config) {
                $queueName = $config['queue'];
                $queueSize = $metrics['queue_sizes'][$queueName] ?? 0;
                $failureRate = $metrics['failure_rates'][$queueName] ?? 0;
                
                $queueHealth = 'healthy';
                
                // Check queue size
                if ($queueSize > 1000) {
                    $queueHealth = 'critical';
                    $status['alerts'][] = "Queue {$queueName} has {$queueSize} pending jobs";
                } elseif ($queueSize > 500) {
                    $queueHealth = 'warning';
                    $status['alerts'][] = "Queue {$queueName} has high load: {$queueSize} jobs";
                }
                
                // Check failure rate
                if ($failureRate > 10) {
                    $queueHealth = 'critical';
                    $status['alerts'][] = "Queue {$queueName} has high failure rate: {$failureRate}%";
                } elseif ($failureRate > 5) {
                    $queueHealth = 'warning';
                    $status['alerts'][] = "Queue {$queueName} has elevated failure rate: {$failureRate}%";
                }
                
                $status['queue_status'][$queueName] = [
                    'health' => $queueHealth,
                    'size' => $queueSize,
                    'failure_rate' => $failureRate,
                    'workers' => $config['workers'],
                ];
                
                // Update overall health
                if ($queueHealth === 'critical') {
                    $status['overall_health'] = 'critical';
                } elseif ($queueHealth === 'warning' && $status['overall_health'] === 'healthy') {
                    $status['overall_health'] = 'warning';
                }
            }

            // Generate recommendations
            if ($status['overall_health'] !== 'healthy') {
                $status['recommendations'][] = 'Consider scaling up workers for overloaded queues';
                $status['recommendations'][] = 'Review failed jobs and fix underlying issues';
                $status['recommendations'][] = 'Monitor queue performance and adjust configuration';
            }

        } catch (\Exception $e) {
            $status['overall_health'] = 'error';
            $status['alerts'][] = 'Failed to check queue health: ' . $e->getMessage();
        }

        return $status;
    }
}