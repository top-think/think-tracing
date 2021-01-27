<?php

namespace think\tracing\middleware;

use Closure;
use think\Request;
use think\tracing\Tracer;
use const OpenTracing\Formats\HTTP_HEADERS;

class TraceRequests
{
    /**
     * @var Tracer
     */
    protected $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $context = $this->tracer->extract(HTTP_HEADERS, $request->header());

        $scope = $this->tracer->startActiveSpan(
            'http.' . strtolower($request->method()) . '.' . $request->url(),
            ['child_of' => $context]
        );

        $response = $next($request);

        $scope->close();

        return $response;
    }

    public function end()
    {
        $this->tracer->flush();
    }

}
