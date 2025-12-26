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
     */
    public function show(): JsonResponse
    {
        $metrics = [];

        // Application version
        $metrics['clearline_version'] = config('app.version', '1.0.0');
        $metrics['laravel_version'] = app()->version();
        $metrics['php_version'] = PHP_VERSION;

        // Edition
        $metrics['edition'] = $this->getEdition();

        // Git SHA (if available)
        $metrics['git_sha'] = $this->getGitSha();

        // Database status
        $metrics['database'] = $this->getDatabaseStatus();

        // Redis status
        $metrics['redis'] = $this->getRedisStatus();

        // Queue status
        $metrics['queue'] = $this->getQueueStatus();

        // Migration status
        $metrics['migrations'] = $this->getMigrationStatus();

        // System info
        $metrics['system'] = [
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];

        return response()->json(['data' => $metrics]);
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
     */
    private function getGitSha(): ?string
    {
        $gitDir = base_path('.git');
        if (is_dir($gitDir)) {
            $headFile = $gitDir.'/HEAD';
            if (file_exists($headFile)) {
                $head = trim(file_get_contents($headFile));
                if (str_starts_with($head, 'ref: ')) {
                    $ref = substr($head, 5);
                    $refFile = $gitDir.'/'.$ref;
                    if (file_exists($refFile)) {
                        return substr(trim(file_get_contents($refFile)), 0, 8);
                    }
                } else {
                    return substr($head, 0, 8);
                }
            }
        }

        return null;
    }

    /**
     * Get database status.
     */
    private function getDatabaseStatus(): array
    {
        try {
            $pdo = DB::connection()->getPdo();

            return [
                'alive' => true,
                'driver' => config('database.default'),
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
            ];
        } catch (\Exception $e) {
            return [
                'alive' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Redis status and metrics.
     */
    private function getRedisStatus(): array
    {
        try {
            $redis = Redis::connection();
            $ping = $redis->ping();

            if ($ping) {
                $info = $redis->info();

                return [
                    'alive' => true,
                    'version' => $info['redis_version'] ?? 'unknown',
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'maxclients' => $info['maxclients'] ?? 0,
                    'used_memory_human' => $info['used_memory_human'] ?? 'unknown',
                    'used_memory_peak_human' => $info['used_memory_peak_human'] ?? 'unknown',
                    'total_system_memory_human' => $info['total_system_memory_human'] ?? 'unknown',
                    'maxmemory' => $info['maxmemory'] ?? 0,
                    'maxmemory_policy' => $info['maxmemory_policy'] ?? 'unknown',
                ];
            }

            return ['alive' => false];
        } catch (\Exception $e) {
            return [
                'alive' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue status.
     */
    private function getQueueStatus(): array
    {
        try {
            $driver = config('queue.default');

            return [
                'driver' => $driver,
                'connection' => config("queue.connections.{$driver}.connection") ?? 'default',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
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
