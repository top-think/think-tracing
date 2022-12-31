<?php
return [
    'default' => 'noop',
    'tracers' => [
        'noop'   => [
            'type' => 'noop',
        ],
        'jaeger' => [
            'type'     => 'jaeger',
            'endpoint' => 'http://localhost:9411/api/traces',
            'async'    => false,
        ],
        'zipkin' => [
            'type'     => 'zipkin',
            'endpoint' => 'http://localhost:9411/api/v2/spans',
            'async'    => false,
        ],
    ],
    'errors'  => true,
    'sql'     => true,
    'redis'   => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ],
];
