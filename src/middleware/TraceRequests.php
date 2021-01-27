<?php

namespace think\tracing\middleware;

use Closure;
use think\Request;
use think\Response;
use think\tracing\Tracer;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_STATUS_CODE;
use const OpenTracing\Tags\HTTP_URL;

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
        $context = $this->tracer->extract(TEXT_MAP, $request->header());

        $scope = $this->tracer->startActiveSpan(
            $request->baseUrl(),
            [
                'child_of' => $context,
                'tags'     => [
                    HTTP_METHOD => $request->method(),
                    HTTP_URL    => $request->url(true),
                ],
            ]
        );

        try {
            /** @var Response $response */
            $response = $next($request);

            $scope->getSpan()->setTag(HTTP_STATUS_CODE, $response->getCode());

            return $response;
        } finally {
            $scope->close();
        }
    }

    public function end()
    {
        $this->tracer->flush();
    }

}
