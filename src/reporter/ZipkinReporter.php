<?php

namespace think\tracing\reporter;

use Exception;
use Zipkin\Reporter;
use Zipkin\Reporters\JsonV2Serializer;

class ZipkinReporter implements Reporter, AsyncReporter
{
    protected $reporter;
    protected $serializer;
    protected $clientFactory;

    protected $options;

    public function __construct(RedisReporter $reporter, $options)
    {
        $this->reporter      = $reporter;
        $this->serializer    = new JsonV2Serializer();
        $this->clientFactory = HttpClientFactory::create();
        $this->options       = $options;
    }

    public function report(array $spans): void
    {
        $this->reporter->push($this->serializer->serialize($spans));
    }

    public function flush()
    {
        $client = $this->clientFactory->build($this->options);

        while (true) {
            $spans = $this->reporter->pop();
            if (!empty($spans)) {
                try {
                    $client($spans);
                } catch (Exception $e) {
                    $this->reporter->push($spans);
                }
            } else {
                sleep(5);
            }
        }
    }
}
