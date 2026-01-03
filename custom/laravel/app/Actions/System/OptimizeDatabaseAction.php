<?php

namespace App\Actions\System;

use App\Repositories\System\SystemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class OptimizeDatabaseAction
{
    use AsAction;

    private SystemRepository $systemRepository;

    public function __construct()
    {
        $this->systemRepository = new SystemRepository();
    }

    /**
     * Perform database optimization tasks
     */
    public function handle(array $options = []): array
    {
        $results = [
            'vacuum_performed' => false,
            'analyze_performed' => false,
            'indexes_optimized' => false,
            'statistics_updated' => false,
            'execution_time' => 0,
            'errors' => [],
        ];

        $startTime = microtime(true);

        try {
            // Vacuum database (PostgreSQL)
            if ($this->shouldVacuum($options)) {
                $results['vacuum_performed'] = $this->performVacuum();
            }

            // Analyze tables
            if ($this->shouldAnalyze($options)) {
                $results['analyze_performed'] = $this->performAnalyze();
            }

            // Optimize indexes
            if ($this->shouldOptimizeIndexes($options)) {
                $results['indexes_optimized'] = $this->optimizeIndexes();
            }

            // Update statistics
            if ($this->shouldUpdateStatistics($options)) {
                $results['statistics_updated'] = $this->updateStatistics();
            }

            $results['execution_time'] = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Database optimization completed', $results);

        } catch (\Exception $e) {
            $results['errors'][] = 'Database optimization failed: ' . $e->getMessage();
            Log::error('Database optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Get database performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return $this->systemRepository->getDatabaseMetrics();
    }

    /**
     * Get slow query log
     */
    public function getSlowQueries(int $limit = 10): array
    {
        return $this->systemRepository->getSlowQueries($limit);
    }

    /**
     * Check if vacuum should be performed
     */
    private function shouldVacuum(array $options): bool
    {
        return ($options['vacuum'] ?? true) && DB::getDriverName() === 'pgsql';
    }

    /**
     * Check if analyze should be performed
     */
    private function shouldAnalyze(array $options): bool
    {
        return $options['analyze'] ?? true;
    }

    /**
     * Check if indexes should be optimized
     */
    private function shouldOptimizeIndexes(array $options): bool
    {
        return $options['optimize_indexes'] ?? true;
    }

    /**
     * Check if statistics should be updated
     */
    private function shouldUpdateStatistics(array $options): bool
    {
        return $options['update_statistics'] ?? true;
    }

    /**
     * Perform database vacuum
     */
    private function performVacuum(): bool
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('VACUUM ANALYZE');
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to perform vacuum', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Perform table analysis
     */
    private function performAnalyze(): bool
    {
        try {
            $tables = $this->systemRepository->getAllTables();
            
            foreach ($tables as $table) {
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement("ANALYZE {$table}");
                } elseif (DB::getDriverName() === 'mysql') {
                    DB::statement("ANALYZE TABLE {$table}");
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to perform analyze', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Optimize database indexes
     */
    private function optimizeIndexes(): bool
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('REINDEX DATABASE ' . DB::getDatabaseName());
                return true;
            } elseif (DB::getDriverName() === 'mysql') {
                $tables = $this->systemRepository->getAllTables();
                foreach ($tables as $table) {
                    DB::statement("OPTIMIZE TABLE {$table}");
                }
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to optimize indexes', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Update database statistics
     */
    private function updateStatistics(): bool
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ANALYZE');
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update statistics', ['error' => $e->getMessage()]);
        }

        return false;
    }
}