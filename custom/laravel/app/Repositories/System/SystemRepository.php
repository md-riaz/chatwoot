<?php

namespace App\Repositories\System;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SystemRepository extends BaseRepository
{
    public function __construct()
    {
        // No specific model for this repository
    }

    /**
     * Lookup IP information using configured provider
     */
    public function lookupIp(string $ip): ?array
    {
        $provider = config('iplookup.provider', 'ipinfo');

        try {
            if ($provider === 'ipinfo') {
                return $this->lookupWithIpInfo($ip);
            }

            if ($provider === 'ip-api') {
                return $this->lookupWithIpApi($ip);
            }

            return null;
        } catch (\Throwable $e) {
            // Do not throw to callers; return null so callers can fallback
            return null;
        }
    }

    /**
     * Lookup IP using ipinfo.io
     */
    private function lookupWithIpInfo(string $ip): ?array
    {
        $token = config('iplookup.providers.ipinfo.token');
        $url = "https://ipinfo.io/{$ip}/json";
        $response = Http::withHeaders(['Accept' => 'application/json'])
            ->get($url, $token ? ['token' => $token] : []);

        if (!$response->ok()) {
            return null;
        }

        $d = $response->json();

        return [
            'ip' => $ip,
            'country' => $d['country'] ?? null,
            'region' => $d['region'] ?? null,
            'city' => $d['city'] ?? null,
            'org' => $d['org'] ?? null,
            'loc' => $d['loc'] ?? null,
            'raw' => $d,
        ];
    }

    /**
     * Lookup IP using ip-api.com
     */
    private function lookupWithIpApi(string $ip): ?array
    {
        $url = "http://ip-api.com/json/{$ip}";
        $response = Http::get($url);
        
        if (!$response->ok()) {
            return null;
        }
        
        $d = $response->json();
        
        return [
            'ip' => $ip,
            'country' => $d['countryCode'] ?? null,
            'region' => $d['regionName'] ?? null,
            'city' => $d['city'] ?? null,
            'org' => $d['isp'] ?? null,
            'raw' => $d,
        ];
    }

    /**
     * Get database performance metrics
     */
    public function getDatabaseMetrics(): array
    {
        try {
            $metrics = [
                'connection_count' => 0,
                'slow_queries' => 0,
                'table_count' => 0,
                'database_size' => 0,
            ];

            if (DB::getDriverName() === 'pgsql') {
                // PostgreSQL metrics
                $result = DB::select("SELECT count(*) as connections FROM pg_stat_activity WHERE state = 'active'");
                $metrics['connection_count'] = $result[0]->connections ?? 0;

                $result = DB::select("SELECT count(*) as tables FROM information_schema.tables WHERE table_schema = 'public'");
                $metrics['table_count'] = $result[0]->tables ?? 0;

                $result = DB::select("SELECT pg_size_pretty(pg_database_size(current_database())) as size");
                $metrics['database_size'] = $result[0]->size ?? '0 bytes';

            } elseif (DB::getDriverName() === 'mysql') {
                // MySQL metrics
                $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
                $metrics['connection_count'] = $result[0]->Value ?? 0;

                $result = DB::select("SELECT COUNT(*) as tables FROM information_schema.tables WHERE table_schema = DATABASE()");
                $metrics['table_count'] = $result[0]->tables ?? 0;

                $result = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS size FROM information_schema.tables WHERE table_schema = DATABASE()");
                $metrics['database_size'] = ($result[0]->size ?? 0) . ' MB';
            }

            return $metrics;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get slow queries
     */
    public function getSlowQueries(int $limit = 10): array
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                return DB::select("
                    SELECT query, calls, total_time, mean_time 
                    FROM pg_stat_statements 
                    ORDER BY total_time DESC 
                    LIMIT ?
                ", [$limit]);
            }
            
            // For other databases, return empty array
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all table names
     */
    public function getAllTables(): array
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                return array_column($tables, 'tablename');
            } elseif (DB::getDriverName() === 'mysql') {
                $tables = DB::select("SHOW TABLES");
                $key = 'Tables_in_' . DB::getDatabaseName();
                return array_column($tables, $key);
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get queue performance metrics
     */
    public function getQueueMetrics(): array
    {
        try {
            $metrics = [
                'pending_jobs' => 0,
                'failed_jobs' => 0,
                'processed_jobs' => 0,
                'failure_rate' => 0,
            ];

            // Get failed jobs count
            $metrics['failed_jobs'] = DB::table('failed_jobs')->count();

            // Get jobs table metrics if it exists
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $metrics['pending_jobs'] = DB::table('jobs')->count();
            }

            // Calculate failure rate
            $total = $metrics['pending_jobs'] + $metrics['failed_jobs'];
            if ($total > 0) {
                $metrics['failure_rate'] = round(($metrics['failed_jobs'] / $total) * 100, 2);
            }

            return $metrics;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get failed jobs statistics
     */
    public function getFailedJobsStats(): array
    {
        try {
            $stats = [
                'total_failed' => 0,
                'oldest_failed_hours' => 0,
                'recent_failures' => 0,
            ];

            $stats['total_failed'] = DB::table('failed_jobs')->count();

            if ($stats['total_failed'] > 0) {
                // Get oldest failed job
                $oldest = DB::table('failed_jobs')
                    ->orderBy('failed_at')
                    ->first();

                if ($oldest) {
                    $stats['oldest_failed_hours'] = now()->diffInHours($oldest->failed_at);
                }

                // Get recent failures (last 24 hours)
                $stats['recent_failures'] = DB::table('failed_jobs')
                    ->where('failed_at', '>=', now()->subDay())
                    ->count();
            }

            return $stats;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get failed jobs count
     */
    public function getFailedJobsCount(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Prune old jobs
     */
    public function pruneOldJobs(int $hours): int
    {
        try {
            $cutoff = now()->subHours($hours);
            
            // Prune from jobs table if it exists
            $count = 0;
            if (DB::getSchemaBuilder()->hasTable('job_batches')) {
                $count += DB::table('job_batches')
                    ->where('created_at', '<', $cutoff)
                    ->delete();
            }

            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Crawl page for metadata
     */
    public function crawlPage(string $url): array
    {
        try {
            $response = Http::timeout(5)->get($url);
        } catch (\Throwable $e) {
            return ['status' => 0, 'title' => null, 'description' => null, 'images' => [], 'url' => $url];
        }

        if (!$response->successful()) {
            return ['status' => $response->status(), 'title' => null, 'description' => null, 'images' => [], 'url' => $url];
        }

        $html = $response->body();

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        $title = null;
        $titleNodes = $doc->getElementsByTagName('title');
        if ($titleNodes->length) {
            $title = trim($titleNodes->item(0)->textContent);
        }

        $description = null;
        $images = [];

        $metas = $doc->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            $prop = strtolower($meta->getAttribute('property'));

            if ($name === 'description' && !$description) {
                $description = $meta->getAttribute('content');
            }

            if ($prop === 'og:description' && !$description) {
                $description = $meta->getAttribute('content');
            }

            if ($prop === 'og:image') {
                $images[] = $meta->getAttribute('content');
            }
        }

        $imgTags = $doc->getElementsByTagName('img');
        foreach ($imgTags as $img) {
            $src = $img->getAttribute('src');
            if ($src) {
                $images[] = $src;
            }
        }

        $images = array_values(array_unique(array_filter($images)));

        return [
            'status' => 200,
            'title' => $title,
            'description' => $description,
            'images' => $images,
            'url' => $url,
        ];
    }
}