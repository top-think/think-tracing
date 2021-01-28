<?php
return [
    'default' => 'zipkin',
    'tracers' => [
        'jaeger' => [
            'type'     => 'jaeger',
            'endpoint' => 'http://localhost:9411/api/traces',
        ],
        'zipkin' => [
            'type'     => 'zipkin',
            'endpoint' => 'http://localhost:9411/api/v2/spans',
        ],
    ],
];
