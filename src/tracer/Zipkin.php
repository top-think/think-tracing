<?php

namespace think\tracing\tracer;

use OpenTracing\Tracer as OTTracer;
use think\helper\Arr;
use think\tracing\reporter\ZipkinReporter;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

class Zipkin extends Driver
{

    protected function createReporter()
    {
        $options = [
            'endpoint_url' => Arr::get($this->config, 'endpoint'),
        ];

        if ($this->redisReporter) {
            return new ZipkinReporter($this->redisReporter, $options);
        }

        return new Http($options);
    }

    protected function createTracer(): OTTracer
    {
        $reporter = $this->createReporter();

        $endpoint = Endpoint::create($this->name, gethostbyname(gethostname()));
        $sampler  = BinarySampler::createAsAlwaysSample();
        $tracing  = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        return new \ZipkinOpenTracing\Tracer($tracing);
    }

}
