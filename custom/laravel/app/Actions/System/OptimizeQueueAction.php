<?php

namespace App\Actions\System;

use App\Repositories\System\SystemRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Lorisleiva\Actions\Concerns\AsAction;

class OptimizeQueueAction
{
    use AsAction;

    private SystemRepository $systemRepository;

    public function __construct()
    {
        $this->systemRepository = new SystemRepository();
    }

    /**
     * Perform queue optimization tasks
     */
    public function handle(array $options = []): array
    {
        $results = [
            'failed_jobs_cleared' => 0,
            'old_jobs_pruned' => 0,
            'queue_restarted' => false,
            'horizon_restarted' => false,
            'execution_time' => 0,
            'errors' => [],
        ];

        $startTime = microtime(true);

        try {
            // Clear failed jobs
            if ($options['clear_failed'] ?? true) {
                $results['failed_jobs_cleared'] = $this->clearFailedJobs();
            }

            // Prune old jobs
            if ($options['prune_old'] ?? true) {
                $results['old_jobs_pruned'] = $this->pruneOldJobs($options['prune_hours'] ?? 48);
            }

            // Restart queue workers
            if ($options['restart_queue'] ?? false) {
                $results['queue_restarted'] = $this->restartQueue();
            }

            // Restart Horizon (if available)
            if ($options['restart_horizon'] ?? false) {
                $results['horizon_restarted'] = $this->restartHorizon();
            }

            $results['execution_time'] = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Queue optimization completed', $results);

        } catch (\Exception $e) {
            $results['errors'][] = 'Queue optimization failed: ' . $e->getMessage();
            Log::error('Queue optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Get queue performance metrics
     */
    public function getQueueMetrics(): array
    {
        return $this->systemRepository->getQueueMetrics();
    }

    /**
     * Get failed jobs statistics
     */
    public function getFailedJobsStats(): array
    {
        return $this->systemRepository->getFailedJobsStats();
    }

    /**
     * Clear failed jobs
     */
    private function clearFailedJobs(): int
    {
        try {
            $count = $this->systemRepository->getFailedJobsCount();
            
            if ($count > 0) {
                Artisan::call('queue:flush');
                Log::info("Cleared {$count} failed jobs");
            }
            
            return $count;
        } catch (\Exception $e) {
            Log::warning('Failed to clear failed jobs', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Prune old completed jobs
     */
    private function pruneOldJobs(int $hours): int
    {
        try {
            $count = $this->systemRepository->pruneOldJobs($hours);
            
            if ($count > 0) {
                Log::info("Pruned {$count} old jobs older than {$hours} hours");
            }
            
            return $count;
        } catch (\Exception $e) {
            Log::warning('Failed to prune old jobs', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Restart queue workers
     */
    private function restartQueue(): bool
    {
        try {
            Artisan::call('queue:restart');
            Log::info('Queue workers restarted');
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to restart queue', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Restart Horizon
     */
    private function restartHorizon(): bool
    {
        try {
            if (class_exists(\Laravel\Horizon\Horizon::class)) {
                Artisan::call('horizon:terminate');
                Log::info('Horizon terminated (will auto-restart)');
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to restart Horizon', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Monitor queue health
     */
    public function monitorQueueHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'metrics' => [],
        ];

        try {
            $metrics = $this->getQueueMetrics();
            $health['metrics'] = $metrics;

            // Check for high failure rate
            if (isset($metrics['failure_rate']) && $metrics['failure_rate'] > 10) {
                $health['status'] = 'warning';
                $health['issues'][] = "High failure rate: {$metrics['failure_rate']}%";
            }

            // Check for old failed jobs
            $failedStats = $this->getFailedJobsStats();
            if (isset($failedStats['oldest_failed_hours']) && $failedStats['oldest_failed_hours'] > 24) {
                $health['status'] = 'warning';
                $health['issues'][] = "Old failed jobs detected (oldest: {$failedStats['oldest_failed_hours']} hours)";
            }

            // Check queue size
            if (isset($metrics['pending_jobs']) && $metrics['pending_jobs'] > 1000) {
                $health['status'] = 'warning';
                $health['issues'][] = "Large queue backlog: {$metrics['pending_jobs']} jobs";
            }

        } catch (\Exception $e) {
            $health['status'] = 'error';
            $health['issues'][] = 'Failed to check queue health: ' . $e->getMessage();
        }

        return $health;
    }
}