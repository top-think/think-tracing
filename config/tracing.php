<?php
return [
    'default' => 'zipkin',
    'tracers' => [
        'zipkin' => [
            'type'    => 'zipkin',
            'host'    => 'localhost',
            'port'    => 9411,
            '128bit'  => false,
            'options' => [],
        ],
    ],
];
