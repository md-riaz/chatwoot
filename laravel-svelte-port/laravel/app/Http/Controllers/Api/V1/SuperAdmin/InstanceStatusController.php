<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class InstanceStatusController extends Controller
{
    /**
     * Show instance status and metrics.
     * Matches Rails SuperAdmin::InstanceStatusesController format exactly.
     */
    public function show(): JsonResponse
    {
        $metrics = [];
    
        // ClearLine version (matching Rails naming)
        $metrics['ClearLine version'] = config('app.version', '4.9.1');
    
        // Git SHA (matching Rails naming)
        $metrics['Git SHA'] = $this->getGitSha();
    
        // PostgreSQL status (matching Rails naming)
        $postgresAlive = $this->getPostgresStatus();
        $metrics['Postgres alive'] = $postgresAlive ? 'true' : 'false';
    
        // Redis metrics (matching Rails naming)
        $this->addRedisMetrics($metrics);
    
        // ClearLine edition (matching Rails naming)
        $metrics['ClearLine edition'] = $this->getEdition();
    
        // Instance meta - Database Migrations (matching Rails naming)
        $migrationStatus = $this->getMigrationStatus();
        $metrics['Database Migrations'] = $migrationStatus['status'];
    
        return response()->json(['data' => $metrics]);
    }

    /**
     * Get PostgreSQL status.
     */
    private function getPostgresStatus(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add Redis metrics to the metrics array.
     */
    private function addRedisMetrics(array &$metrics): void
    {
        try {
            $redis = Redis::connection();
            $ping = $redis->ping();

            if ($ping == 'PONG') {
                $info = $redis->info();

                $metrics['Redis alive'] = 'true';
                $metrics['Redis version'] = $info['redis_version'] ?? 'unknown';
                $metrics['Redis number of connected clients'] = $info['connected_clients'] ?? 0;
                $metrics["Redis 'maxclients' setting"] = $info['maxclients'] ?? 0;
                $metrics['Redis memory used'] = $info['used_memory_human'] ?? 'unknown';
                $metrics['Redis memory peak'] = $info['used_memory_peak_human'] ?? 'unknown';
                $metrics['Redis total memory available'] = $info['total_system_memory_human'] ?? 'unknown';
                $metrics["Redis 'maxmemory' setting"] = $info['maxmemory'] ?? 0;
                $metrics["Redis 'maxmemory_policy' setting"] = $info['maxmemory_policy'] ?? 'unknown';
            } else {
                $metrics['Redis alive'] = 'false';
            }
        } catch (\Exception $e) {
            $metrics['Redis alive'] = 'false';
        }
    }

    /**
     * Get the edition type.
     */
    private function getEdition(): string
    {
        // Could be extended for enterprise/custom editions
        return 'Community';
    }

    /**
     * Get Git SHA if available.
     * Matches Rails git_sha.rb initializer logic.
     */
    private function getGitSha(): ?string
    {
        // Try git command first
        if (is_dir(base_path('.git'))) {
            $gitCommand = 'git rev-parse HEAD';
            $sha = trim(shell_exec($gitCommand) ?: '');
            
            if (!empty($sha) && strlen($sha) >= 8) {
                return substr($sha, 0, 8);
            }
        }
        
        // Check for .git_sha file
        $gitShaFile = base_path('.git_sha');
        if (file_exists($gitShaFile)) {
            $sha = trim(file_get_contents($gitShaFile));
            if (!empty($sha)) {
                return substr($sha, 0, 8);
            }
        }
        
        // Check for Heroku environment
        $herokuSha = env('HEROKU_SLUG_COMMIT');
        if ($herokuSha) {
            return substr($herokuSha, 0, 8);
        }
        
        return 'unknown';
    }

    /**
     * Get migration status.
     */
    private function getMigrationStatus(): array
    {
        try {
            $migrator = app('migrator');
            $ran = $migrator->getRepository()->getRan();
            $pending = count($migrator->getMigrationFiles(database_path('migrations'))) - count($ran);

            return [
                'status' => $pending > 0 ? 'pending' : 'completed',
                'ran_count' => count($ran),
                'pending_count' => $pending,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }
}
