<?php

namespace think\tracing\tracer;

use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Sender\UdpSender;
use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\ThriftUdpTransport;
use think\helper\Arr;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TBufferedTransport;

class Jaeger extends Driver
{

    protected function createReporter()
    {
        $udp             = new ThriftUdpTransport(
            Arr::get($this->config, 'host', 'localhost'),
            Arr::get($this->config, 'port', 5775)
        );
        $maxBufferLength = Arr::get($this->config, 'max_buffer_length', 6400);
        $transport       = new TBufferedTransport($udp, $maxBufferLength, $maxBufferLength);
        $transport->open();

        $protocol = new TCompactProtocol($transport);
        $client   = new AgentClient($protocol);

        $sender = new UdpSender($client, $maxBufferLength);

        return new RemoteReporter($sender);
    }

    protected function createTracer()
    {
        $reporter = $this->createReporter();
        $sampler  = new ConstSampler();

        return new \Jaeger\Tracer(
            $this->name,
            $reporter,
            $sampler
        );
    }
}
