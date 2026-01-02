<?php

namespace App\Console\Commands;

use App\Services\ConfigCacheService;
use App\Services\DatabaseOptimizationService;
use App\Services\QueueOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Performance Optimization Command
 * 
 * Applies comprehensive performance optimizations including configuration caching,
 * database query optimization, and background job performance tuning.
 */
class OptimizePerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'chatwoot:optimize-performance 
                            {--config : Optimize configuration caching}
                            {--database : Optimize database queries and indexes}
                            {--queue : Optimize background job processing}
                            {--all : Apply all optimizations}
                            {--force : Force optimization even in production}';

    /**
     * The console command description.
     */
    protected $description = 'Apply comprehensive performance optimizations for production deployment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Chatwoot Performance Optimization...');
        
        if (!$this->option('force') && app()->environment('production')) {
            if (!$this->confirm('You are running this in production. Are you sure you want to continue?')) {
                $this->info('Optimization cancelled.');
                return 0;
            }
        }

        $results = [
            'config' => null,
            'database' => null,
            'queue' => null,
            'overall_success' => true,
        ];

        // Apply optimizations based on options
        if ($this->option('all') || $this->option('config')) {
            $results['config'] = $this->optimizeConfiguration();
        }

        if ($this->option('all') || $this->option('database')) {
            $results['database'] = $this->optimizeDatabase();
        }

        if ($this->option('all') || $this->option('queue')) {
            $results['queue'] = $this->optimizeQueue();
        }

        // If no specific options, show help
        if (!$this->option('all') && !$this->option('config') && !$this->option('database') && !$this->option('queue')) {
            $this->showOptimizationStatus();
            return 0;
        }

        // Display results
        $this->displayResults($results);

        // Log optimization results
        Log::info('Performance optimization completed', $results);

        return $results['overall_success'] ? 0 : 1;
    }

    /**
     * Optimize configuration caching.
     */
    private function optimizeConfiguration(): array
    {
        $this->info('📋 Optimizing Configuration Caching...');
        
        try {
            // Warm up configuration cache
            $warmUpResults = ConfigCacheService::warmUp();
            
            // Check production optimizations
            $prodOptimizations = ConfigCacheService::optimizeForProduction();
            
            // Preload frequent configurations
            ConfigCacheService::preloadFrequentConfigs();
            
            $this->info("✅ Configuration cache warmed up: {$warmUpResults['cached_keys']} keys cached");
            
            if (!empty($prodOptimizations['errors'])) {
                $this->warn('⚠️  Production optimization warnings:');
                foreach ($prodOptimizations['errors'] as $error) {
                    $this->warn("   • {$error}");
                }
            }

            return [
                'success' => true,
                'cached_keys' => $warmUpResults['cached_keys'],
                'execution_time' => $warmUpResults['execution_time'],
                'production_ready' => empty($prodOptimizations['errors']),
                'errors' => $warmUpResults['errors'],
            ];

        } catch (\Exception $e) {
            $this->error("❌ Configuration optimization failed: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize database performance.
     */
    private function optimizeDatabase(): array
    {
        $this->info('🗄️  Optimizing Database Performance...');
        
        try {
            // Optimize database connections
            $connectionResults = DatabaseOptimizationService::optimizeConnections();
            
            // Create optimized indexes
            $this->info('   Creating optimized indexes...');
            DatabaseOptimizationService::optimizeConversationQueries();
            DatabaseOptimizationService::optimizeMessageQueries();
            DatabaseOptimizationService::optimizeReportingQueries();
            
            // Get performance metrics
            $metrics = DatabaseOptimizationService::getPerformanceMetrics();
            
            // Analyze query performance
            $analysis = DatabaseOptimizationService::analyzeQueryPerformance();
            
            $this->info("✅ Database optimization completed");
            $this->info("   • Connection pools configured: {$connectionResults['connection_pools_configured']}");
            $this->info("   • Active connections: {$metrics['connection_count']}");
            $this->info("   • Slow queries detected: " . count($analysis['slow_queries']));
            
            if (!empty($analysis['slow_queries'])) {
                $this->warn('⚠️  Slow queries detected:');
                foreach ($analysis['slow_queries'] as $query) {
                    $this->warn("   • {$query['name']}: {$query['execution_time']}ms");
                }
            }

            return [
                'success' => true,
                'connections_optimized' => $connectionResults['connection_pools_configured'],
                'metrics' => $metrics,
                'slow_queries' => count($analysis['slow_queries']),
                'suggestions' => $analysis['suggestions'],
                'errors' => $connectionResults['errors'],
            ];

        } catch (\Exception $e) {
            $this->error("❌ Database optimization failed: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize queue performance.
     */
    private function optimizeQueue(): array
    {
        $this->info('⚡ Optimizing Background Job Processing...');
        
        try {
            // Optimize queue configuration
            $queueResults = QueueOptimizationService::optimizeQueueConfiguration();
            
            // Get queue metrics
            $metrics = QueueOptimizationService::getQueueMetrics();
            
            // Get health status
            $health = QueueOptimizationService::getQueueHealthStatus();
            
            // Auto-scaling analysis
            $scaling = QueueOptimizationService::autoScaleWorkers();
            
            // Queue cleanup
            $cleanup = QueueOptimizationService::optimizeQueueCleanup();
            
            $this->info("✅ Queue optimization completed");
            $this->info("   • Queues optimized: {$queueResults['queues_optimized']}");
            $this->info("   • Workers configured: {$queueResults['workers_configured']}");
            $this->info("   • Overall health: {$health['overall_health']}");
            $this->info("   • Failed jobs cleared: {$cleanup['failed_jobs_cleared']}");
            
            if (!empty($health['alerts'])) {
                $this->warn('⚠️  Queue health alerts:');
                foreach ($health['alerts'] as $alert) {
                    $this->warn("   • {$alert}");
                }
            }

            if (!empty($scaling['recommendations'])) {
                $this->info('💡 Scaling recommendations:');
                foreach ($scaling['recommendations'] as $queue => $recommendation) {
                    $this->info("   • {$queue}: {$recommendation['action']} to {$recommendation['recommended_workers']} workers");
                }
            }

            return [
                'success' => true,
                'queues_optimized' => $queueResults['queues_optimized'],
                'workers_configured' => $queueResults['workers_configured'],
                'health_status' => $health['overall_health'],
                'cleanup_results' => $cleanup,
                'scaling_recommendations' => $scaling['recommendations'],
                'errors' => $queueResults['errors'],
            ];

        } catch (\Exception $e) {
            $this->error("❌ Queue optimization failed: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Show current optimization status.
     */
    private function showOptimizationStatus(): void
    {
        $this->info('📊 Current Performance Status:');
        $this->newLine();

        // Configuration status
        $this->info('Configuration Caching:');
        $configStats = ConfigCacheService::getStats();
        $this->info("   • Cached keys: {$configStats['cached_keys']}");
        $this->info("   • Cache hit ratio: {$configStats['cache_hit_ratio']}%");
        $this->info("   • Memory usage: " . number_format($configStats['memory_usage'] / 1024, 2) . " KB");
        $this->newLine();

        // Database status
        $this->info('Database Performance:');
        $dbMetrics = DatabaseOptimizationService::getPerformanceMetrics();
        $this->info("   • Active connections: {$dbMetrics['connection_count']}");
        $this->info("   • Slow queries: {$dbMetrics['slow_queries']}");
        foreach ($dbMetrics['table_sizes'] as $table => $size) {
            $this->info("   • {$table} size: {$size} MB");
        }
        $this->newLine();

        // Queue status
        $this->info('Queue Performance:');
        $queueHealth = QueueOptimizationService::getQueueHealthStatus();
        $this->info("   • Overall health: {$queueHealth['overall_health']}");
        foreach ($queueHealth['queue_status'] as $queue => $status) {
            $this->info("   • {$queue}: {$status['size']} jobs, {$status['workers']} workers");
        }
        $this->newLine();

        $this->info('Use --all to apply all optimizations, or specific flags:');
        $this->info('  --config    Optimize configuration caching');
        $this->info('  --database  Optimize database queries and indexes');
        $this->info('  --queue     Optimize background job processing');
    }

    /**
     * Display optimization results.
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('🎯 Optimization Results Summary:');
        $this->newLine();

        $overallSuccess = true;

        foreach ($results as $type => $result) {
            if ($type === 'overall_success' || $result === null) {
                continue;
            }

            $status = $result['success'] ? '✅' : '❌';
            $this->info("{$status} " . ucfirst($type) . ' Optimization');

            if ($result['success']) {
                switch ($type) {
                    case 'config':
                        $this->info("   • {$result['cached_keys']} configuration keys cached");
                        $this->info("   • Execution time: {$result['execution_time']}ms");
                        break;
                    case 'database':
                        $this->info("   • {$result['connections_optimized']} connection pools optimized");
                        $this->info("   • {$result['slow_queries']} slow queries detected");
                        break;
                    case 'queue':
                        $this->info("   • {$result['queues_optimized']} queues optimized");
                        $this->info("   • {$result['workers_configured']} workers configured");
                        $this->info("   • Health status: {$result['health_status']}");
                        break;
                }
            } else {
                $this->error("   Error: {$result['error']}");
                $overallSuccess = false;
            }

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->warn("   Warning: {$error}");
                }
            }

            $this->newLine();
        }

        $results['overall_success'] = $overallSuccess;

        if ($overallSuccess) {
            $this->info('🎉 All optimizations completed successfully!');
            $this->info('💡 Your Chatwoot Laravel system is now optimized for production performance.');
        } else {
            $this->error('⚠️  Some optimizations failed. Check the logs for details.');
        }

        $this->newLine();
        $this->info('📈 Performance Tips:');
        $this->info('  • Monitor queue health regularly with: php artisan horizon:status');
        $this->info('  • Clear caches when needed with: php artisan cache:clear');
        $this->info('  • Run database maintenance: php artisan db:optimize');
        $this->info('  • Monitor logs for performance issues');
    }
}