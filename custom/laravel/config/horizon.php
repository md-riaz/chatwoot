<?php

return [

    'domain' => env('HORIZON_DOMAIN'),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => env('HORIZON_USE', 'default'),

    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
        'redis:deliveries' => 60,
        'redis:conversations' => 60,
        'redis:sla' => 60,
        'redis:notifications' => 60,
        'redis:campaigns' => 60,
        'redis:imports' => 120,
        'redis:attachments' => 120,
        'redis:webhooks' => 60,
        'redis:reports' => 120,
    ],

    'trim' => [
        'recent' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 2160,
    ],

    'fast_termination' => false,

    'memory_limit' => 64,

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => env('HORIZON_QUEUE', 'redis'),
                'queue' => [
                    'deliveries',
                    'sla',
                    'conversations',
                    'notifications',
                    'campaigns',
                    'imports',
                    'attachments',
                    'webhooks',
                    'reports',
                    'default',
                ],
                'balance' => 'auto',
                'maxProcesses' => env('HORIZON_MAX_PROCESSES', 10),
                'maxTime' => 360,
                'maxJobs' => 1000,
                'nice' => 0,
                'timeout' => 180,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => env('HORIZON_QUEUE', 'redis'),
                'queue' => [
                    'deliveries',
                    'sla',
                    'conversations',
                    'notifications',
                    'campaigns',
                    'imports',
                    'attachments',
                    'webhooks',
                    'reports',
                    'default',
                ],
                'balance' => 'simple',
                'maxProcesses' => env('HORIZON_MAX_PROCESSES', 3),
                'maxTime' => 360,
                'maxJobs' => 500,
                'nice' => 0,
                'timeout' => 180,
            ],
        ],
    ],
];
