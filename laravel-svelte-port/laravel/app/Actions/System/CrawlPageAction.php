<?php

namespace App\Actions\System;

use App\Repositories\System\SystemRepository;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class CrawlPageAction
{
    use AsAction;

    private SystemRepository $systemRepository;

    public function __construct()
    {
        $this->systemRepository = new SystemRepository();
    }

    /**
     * Fetch page metadata (title, description, images)
     */
    public function handle(string $url): array
    {
        $cacheKey = 'pagecrawler:' . md5($url);
        $ttl = config('crawler.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () use ($url) {
            return $this->systemRepository->crawlPage($url);
        });
    }

    /**
     * Clear page crawler cache
     */
    public function clearCache(string $url = null): void
    {
        if ($url) {
            $cacheKey = 'pagecrawler:' . md5($url);
            Cache::forget($cacheKey);
        } else {
            // Clear all page crawler cache
            Cache::flush(); // Simplified - could be optimized with pattern matching
        }
    }
}