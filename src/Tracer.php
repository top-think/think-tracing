<?php

namespace think\tracing;

use InvalidArgumentException;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Sender\UdpSender;
use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\ThriftUdpTransport;
use OpenTracing\Scope;
use OpenTracing\ScopeManager;
use OpenTracing\Span;
use OpenTracing\SpanContext as OTSpanContext;
use think\helper\Arr;
use think\Manager;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TBufferedTransport;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

/**
 * Class Tracer
 * @package think\tracing
 */
class Tracer extends Manager implements \OpenTracing\Tracer
{

    /**
     * 获取配置
     * @param null|string $name 名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('tracing.' . $name, $default);
        }

        return $this->app->config->get('tracing');
    }

    protected function resolveConfig(string $name)
    {
        return $this->getTracerConfig($name);
    }

    protected function resolveParams($name): array
    {
        return array_merge([$name], parent::resolveParams($name));
    }

    protected function createJaegerDriver($name, $config)
    {
        $udp             = new ThriftUdpTransport(
            Arr::get($config, 'host', 'localhost'),
            Arr::get($config, 'port', 5775)
        );
        $maxBufferLength = Arr::get($config, 'max_buffer_length', 6400);
        $transport       = new TBufferedTransport($udp, $maxBufferLength, $maxBufferLength);
        $transport->open();

        $protocol = new TCompactProtocol($transport);
        $client   = new AgentClient($protocol);

        $sender = new UdpSender($client, $maxBufferLength);

        $reporter = new RemoteReporter($sender);

        $sampler = new ConstSampler();

        return new \Jaeger\Tracer(
            $name,
            $reporter,
            $sampler
        );
    }

    protected function createZipkinDriver($name, $config)
    {
        $endpoint = Endpoint::create($name, gethostbyname(gethostname()));

        $reporter = new Http([
            'endpoint_url' => Arr::get($config, 'endpoint'),
        ]);
        $sampler  = BinarySampler::createAsAlwaysSample();
        $tracing  = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingTraceId128bits(Arr::get($config, '128bit', false))
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        return new \ZipkinOpenTracing\Tracer($tracing);
    }

    /**
     * 获取驱动配置
     * @param string $tracer
     * @param string|null $name
     * @param null $default
     * @return array
     */
    public function getTracerConfig(string $tracer, string $name = null, $default = null)
    {
        if ($config = $this->getConfig("tracers.{$tracer}")) {
            return Arr::get($config, $name, $default);
        }

        throw new InvalidArgumentException("Tracer [$tracer] not found.");
    }

    /**
     * @param null $name
     * @return \OpenTracing\Tracer
     */
    public function tracer($name = null)
    {
        return $this->driver($name);
    }

    protected function resolveType(string $name)
    {
        return $this->getTracerConfig($name, 'type', 'zipkin');
    }

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

    public function inject(OTSpanContext $spanContext, string $format, &$carrier): void
    {
        $this->tracer()->inject($spanContext, $format, $carrier);
    }

    public function getScopeManager(): ScopeManager
    {
        return $this->tracer()->getScopeManager();
    }

    public function getActiveSpan(): ?Span
    {
        return $this->tracer()->getActiveSpan();
    }

    public function startActiveSpan(string $operationName, $options = []): Scope
    {
        return $this->tracer()->startActiveSpan($operationName, $options);
    }

    public function startSpan(string $operationName, $options = []): Span
    {
        return $this->tracer()->startSpan($operationName, $options);
    }

    public function extract(string $format, $carrier): ?OTSpanContext
    {
        return $this->tracer()->extract($format, $carrier);
    }

    public function flush(): void
    {
        $this->tracer()->flush();
    }
}
