<?php

namespace Tests\Unit\Services;

use App\Services\IpLookupService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IpLookupServiceTest extends TestCase
{
    public function test_lookup_ipinfo()
    {
        Cache::forget('iplookup:8.8.8.8');

        Http::fake([
            'https://ipinfo.io/8.8.8.8/*' => Http::response([
                'ip' => '8.8.8.8',
                'country' => 'US',
                'region' => 'California',
                'city' => 'Mountain View',
                'org' => 'AS15169 Google LLC',
            ], 200),
        ]);

        config(['iplookup.provider' => 'ipinfo']);

        $svc = new IpLookupService();
        $res = $svc->lookup('8.8.8.8');

        $this->assertNotNull($res);
        $this->assertEquals('US', $res['country']);
        $this->assertEquals('Mountain View', $res['city']);

        // second call should hit cache (Http::fake will remain but function will return cached)
        $res2 = $svc->lookup('8.8.8.8');
        $this->assertEquals($res, $res2);
    }
}
