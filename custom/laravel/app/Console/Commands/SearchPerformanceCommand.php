<?php

namespace App\Console\Commands;

use App\Services\SearchPerformanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SearchPerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'search:performance 
                            {action : The action to perform (check|benchmark|stats|optimize)}
                            {--query=test : Sample query for benchmarking}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Manage and monitor search performance optimizations';

    private SearchPerformanceService $performanceService;

    public function __construct(SearchPerformanceService $performanceService)
    {
        parent::__construct();
        $this->performanceService = $performanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'check' => $this->checkIndexes(),
            'benchmark' => $this->runBenchmark(),
            'stats' => $this->showStats(),
            'optimize' => $this->optimizeSearch(),
            default => $this->showHelp(),
        };
    }

    /**
     * Check search indexes status.
     */
    private function checkIndexes(): int
    {
        $this->info('Checking search indexes...');
        
        $results = $this->performanceService->checkSearchIndexes();
        
        $this->line('Database Driver: ' . $results['database_driver']);
        $this->newLine();

        if (isset($results['error'])) {
            $this->error('Error: ' . $results['error']);
            return 1;
        }

        // Display index status
        $this->info('Index Status:');
        foreach ($results['indexes'] as $indexName => $indexInfo) {
            $status = $indexInfo['exists'] ? '✓' : '✗';
            $this->line("  {$status} {$indexName}");
            
            if ($this->option('verbose') && isset($indexInfo['performance_impact'])) {
                $this->line("    Impact: {$indexInfo['performance_impact']}");
            }
            
            if (isset($indexInfo['error'])) {
                $this->warn("    Error: {$indexInfo['error']}");
            }
        }

        $this->newLine();

        // Display recommendations
        if (!empty($results['recommendations'])) {
            $this->warn('Recommendations:');
            foreach ($results['recommendations'] as $recommendation) {
                $priority = strtoupper($recommendation['priority']);
                $this->line("  [{$priority}] {$recommendation['action']}");
                
                if ($this->option('verbose') && isset($recommendation['impact'])) {
                    $this->line("    Impact: {$recommendation['impact']}");
                }
            }
        } else {
            $this->info('All search indexes are properly configured!');
        }

        return 0;
    }

    /**
     * Run search performance benchmark.
     */
    private function runBenchmark(): int
    {
        $query = $this->option('query');
        $this->info("Running search benchmark with query: '{$query}'");
        
        $results = $this->performanceService->benchmarkSearchPerformance($query);
        
        if (isset($results['error'])) {
            $this->error('Benchmark failed: ' . $results['error']);
            return 1;
        }

        $this->newLine();
        $this->info('Benchmark Results:');
        
        foreach ($results as $testName => $testResult) {
            if ($testResult['status'] === 'success') {
                $this->line("  ✓ {$testResult['description']}: {$testResult['execution_time_ms']}ms ({$testResult['result_count']} results)");
            } else {
                $this->error("  ✗ {$testResult['description']}: {$testResult['error']}");
            }
        }

        // Performance analysis
        $this->newLine();
        $this->analyzePerformance($results);

        return 0;
    }

    /**
     * Show search statistics.
     */
    private function showStats(): int
    {
        $this->info('Gathering search statistics...');
        
        $stats = $this->performanceService->getSearchStatistics();
        
        if (isset($stats['error'])) {
            $this->error('Failed to get statistics: ' . $stats['error']);
            return 1;
        }

        $this->line('Database Driver: ' . $stats['database_driver']);
        $this->newLine();

        // Table sizes
        if (isset($stats['table_sizes'])) {
            $this->info('Table Sizes:');
            foreach ($stats['table_sizes'] as $table => $sizeInfo) {
                if (isset($sizeInfo['error'])) {
                    $this->warn("  {$table}: Error - {$sizeInfo['error']}");
                } else {
                    $size = $sizeInfo->size ?? $sizeInfo->size_mb . 'MB';
                    $this->line("  {$table}: {$size}");
                }
            }
            $this->newLine();
        }

        // Index usage (PostgreSQL only)
        if (isset($stats['index_usage']) && is_array($stats['index_usage']) && !isset($stats['index_usage']['note'])) {
            $this->info('Search Index Usage:');
            foreach ($stats['index_usage'] as $index) {
                $this->line("  {$index->indexname}: {$index->idx_tup_read} reads, {$index->idx_tup_fetch} fetches");
            }
            $this->newLine();
        } elseif (isset($stats['index_usage']['note'])) {
            $this->warn($stats['index_usage']['note']);
            $this->newLine();
        }

        // Search configuration
        if (isset($stats['search_config']) && $this->option('verbose')) {
            $this->info('Search Configuration:');
            foreach ($stats['search_config'] as $key => $value) {
                $this->line("  {$key}: " . json_encode($value));
            }
        }

        return 0;
    }

    /**
     * Optimize search performance.
     */
    private function optimizeSearch(): int
    {
        $this->info('Optimizing search performance...');
        
        // Clear performance cache
        $this->performanceService->clearPerformanceCache();
        $this->line('✓ Cleared performance cache');
        
        // Analyze current state
        $indexResults = $this->performanceService->checkSearchIndexes();
        
        if (!empty($indexResults['recommendations'])) {
            $this->warn('Found optimization opportunities:');
            
            foreach ($indexResults['recommendations'] as $recommendation) {
                if ($recommendation['type'] === 'missing_index') {
                    $this->line("  - {$recommendation['action']}");
                    
                    if ($this->confirm("Would you like to run migrations to create missing indexes?")) {
                        $this->call('migrate');
                        $this->info('✓ Migrations completed');
                    }
                    break; // Only ask once for migrations
                }
            }
        }

        // Vacuum/optimize database (PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            if ($this->confirm('Would you like to vacuum analyze search tables for better performance?')) {
                $this->info('Running VACUUM ANALYZE on search tables...');
                
                $tables = ['messages', 'conversations', 'contacts'];
                foreach ($tables as $table) {
                    try {
                        DB::statement("VACUUM ANALYZE {$table}");
                        $this->line("✓ Optimized {$table}");
                    } catch (\Exception $e) {
                        $this->warn("Failed to optimize {$table}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info('Search optimization completed!');
        return 0;
    }

    /**
     * Analyze benchmark performance results.
     */
    private function analyzePerformance(array $results): void
    {
        $this->info('Performance Analysis:');
        
        foreach ($results as $testName => $testResult) {
            if ($testResult['status'] !== 'success') {
                continue;
            }
            
            $time = $testResult['execution_time_ms'];
            
            if ($time < 10) {
                $this->line("  ✓ {$testName}: Excellent performance ({$time}ms)");
            } elseif ($time < 50) {
                $this->line("  ✓ {$testName}: Good performance ({$time}ms)");
            } elseif ($time < 200) {
                $this->warn("  ⚠ {$testName}: Acceptable performance ({$time}ms) - consider optimization");
            } else {
                $this->error("  ✗ {$testName}: Poor performance ({$time}ms) - optimization needed");
            }
        }
    }

    /**
     * Show help information.
     */
    private function showHelp(): int
    {
        $this->error('Invalid action. Available actions:');
        $this->line('  check     - Check search indexes status');
        $this->line('  benchmark - Run search performance benchmark');
        $this->line('  stats     - Show search statistics');
        $this->line('  optimize  - Optimize search performance');
        $this->newLine();
        $this->line('Examples:');
        $this->line('  php artisan search:performance check');
        $this->line('  php artisan search:performance benchmark --query="hello world"');
        $this->line('  php artisan search:performance stats --verbose');
        $this->line('  php artisan search:performance optimize');
        
        return 1;
    }
}