<?php

namespace think\tracing;

use InvalidArgumentException;
use think\helper\Arr;
use think\Manager;
use think\tracing\reporter\RedisReporter;
use think\tracing\tracer\Driver;

/**
 * Class Tracer
 * @package think\tracing
 */
class Tracer extends Manager implements \OpenTracing\Tracer
{
    use InteractsWithTracer;

    protected $namespace = '\\think\\tracing\\tracer\\';

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

    protected function createDriver(string $name)
    {
        /** @var Driver $driver */
        $driver = parent::createDriver($name);

        $async = $this->getTracerConfig($name, 'async', false);

        if ($async) {
            $driver->setRedisReporter($this->createRedisReporter($name));
        }

        return $driver;
    }

    /**
     * @param $name
     * @return RedisReporter
     */
    protected function createRedisReporter($name)
    {
        $config = $this->getConfig('redis', []);
        return $this->app->make(RedisReporter::class, [$name, $config]);
    }

    /**
     * 获取驱动配置
     * @param string $tracer
     * @param string|null $name
     * @param null $default
     * @return mixed
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
     * @return Driver
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

}
