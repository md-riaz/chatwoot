<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * IP Lookup Service
 * - Supports multiple providers via config/iplookup.php
 * - Caches results
 *
 * @see app/services/ip_lookup_service.rb
 */
class IpLookupService
{
    /**
     * Lookup IP metadata and cache the result.
     * Returns null if provider fails.
     *
     * @param string $ip
     * @return array|null
     */
    public function lookup(string $ip): ?array
    {
        $cacheKey = "iplookup:{$ip}";
        $ttl = config('iplookup.ttl', 86400);

        return Cache::remember($cacheKey, $ttl, function () use ($ip) {
            $provider = config('iplookup.provider', 'ipinfo');

            try {
                if ($provider === 'ipinfo') {
                    $token = config('iplookup.providers.ipinfo.token');
                    $url = "https://ipinfo.io/{$ip}/json";
                    $response = Http::withHeaders(['Accept' => 'application/json'])->get($url, $token ? ['token' => $token] : []);

                    if (! $response->ok()) {
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

                if ($provider === 'ip-api') {
                    $url = "http://ip-api.com/json/{$ip}";
                    $response = Http::get($url);
                    if (! $response->ok()) {
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

                return null;
            } catch (\Throwable $e) {
                // Do not throw to callers; return null so callers can fallback
                return null;
            }
        });
    }
}
