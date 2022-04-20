<?php

namespace think\tracing\reporter;

use RuntimeException;
use Zipkin\Reporter;
use Zipkin\Reporters\Http\CurlFactory;
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
        $this->clientFactory = CurlFactory::create();
        $this->options       = $options;
    }

    public function report(array $spans): void
    {
        $this->reporter->push(serialize($spans));
    }

    public function flush()
    {
        $client = $this->clientFactory->build($this->options);

        while (true) {
            $list  = $this->reporter->pop();
            $spans = array_reduce($list, function ($carry, $item) {
                return array_merge($carry, unserialize($item));
            }, []);

            $payload = $this->serializer->serialize($spans);
            try {
                $client($payload);
            } catch (RuntimeException $e) {

            }
            sleep(5);
        }
    }
}
