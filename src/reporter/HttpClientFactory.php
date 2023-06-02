<?php

namespace think\tracing\reporter;

use GuzzleHttp\Client;
use think\helper\Arr;
use Zipkin\Reporters\Http\ClientFactory;

class HttpClientFactory implements ClientFactory
{

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function build(array $options): callable
    {
        $client = new Client([
            'timeout' => Arr::get($options, 'timeout', 10),
        ]);

        return static function (string $payload) use ($client, $options): void {
            $additionalHeaders = $options['headers'] ?? [];

            $requiredHeaders = [
                'Content-Type'   => 'application/json',
                'Content-Length' => strlen($payload),
                'b3'             => '0',
            ];

            $headers = array_merge($additionalHeaders, $requiredHeaders);

            $client->post($options['endpoint_url'], [
                'body'    => $payload,
                'headers' => $headers,
            ]);
        };
    }
}
