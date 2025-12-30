<?php

return [
    'provider' => env('IPLOOKUP_PROVIDER', 'ipinfo'),
    'ttl' => env('IPLOOKUP_TTL', 86400),
    'providers' => [
        'ipinfo' => [
            'token' => env('IPINFO_TOKEN', null),
        ],
        'ip-api' => [],
    ],
];
