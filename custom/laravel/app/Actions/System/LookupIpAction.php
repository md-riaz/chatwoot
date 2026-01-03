<?php

namespace App\Actions\System;

use App\Repositories\System\SystemRepository;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class LookupIpAction
{
    use AsAction;

    private SystemRepository $systemRepository;

    public function __construct()
    {
        $this->systemRepository = new SystemRepository();
    }

    /**
     * Lookup IP metadata and cache the result
     */
    public function handle(string $ip): ?array
    {
        $cacheKey = "iplookup:{$ip}";
        $ttl = config('iplookup.ttl', 86400);

        return Cache::remember($cacheKey, $ttl, function () use ($ip) {
            return $this->systemRepository->lookupIp($ip);
        });
    }
}