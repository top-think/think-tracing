<?php

namespace think\tracing\tracer;

use OpenTracing\NoopTracer;
use OpenTracing\Tracer as OTTracer;

class Noop extends Driver
{

    protected function createReporter()
    {
        return null;
    }

    protected function createTracer(): OTTracer
    {
        return new NoopTracer();
    }
}
