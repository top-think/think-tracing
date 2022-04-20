<?php

namespace think\tracing\tracer;

use think\tracing\InteractsWithTracer;
use think\tracing\reporter\AsyncReporter;
use think\tracing\reporter\RedisReporter;

abstract class Driver implements \OpenTracing\Tracer
{
    use InteractsWithTracer;

    protected $tracer;

    protected $name;
    protected $config;

    protected $redisReporter;

    public function __construct($name, $config = [])
    {
        $this->name   = $name;
        $this->config = $config;
    }

    public function setRedisReporter(RedisReporter $reporter)
    {
        $this->redisReporter = $reporter;
        return $this;
    }

    abstract protected function createReporter();

    abstract protected function createTracer();

    protected function tracer()
    {
        if (empty($this->tracer)) {
            $this->tracer = $this->createTracer();
        }
        return $this->tracer;
    }

    /**
     * 异步上报数据
     * @return void
     */
    public function report()
    {
        $reporter = $this->createReporter();

        if ($reporter instanceof AsyncReporter) {
            $reporter->flush();
        }
    }

}
