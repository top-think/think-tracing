<?php

namespace think\tracing;

use OpenTracing\Scope;
use OpenTracing\ScopeManager;
use OpenTracing\Span;
use OpenTracing\SpanContext as OTSpanContext;

trait InteractsWithTracer
{
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
