<?php
return [
    'default' => 'zipkin',
    'tracers' => [
        'zipkin' => [
            'type'     => 'zipkin',
            'endpoint' => 'http://localhost:9411/api/v2/spans',
            '128bit'   => false,
        ],
    ],
];
