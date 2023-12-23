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

    /** @var \OpenTracing\Scope */
    protected $scope;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $context = $this->tracer->extract(TEXT_MAP, $request->header());

        $this->scope = $this->tracer->startActiveSpan(
            "http:" . $request->baseUrl(),
            [
                'child_of' => $context,
                'tags'     => [
                    HTTP_METHOD   => $request->method(),
                    HTTP_URL      => $request->baseUrl(true),
                    'http.ip'     => $request->ip(),
                    'http.params' => json_encode($request->param(), JSON_ERROR_NONE | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ]
        );

        /** @var Response $response */
        $response = $next($request);

        $span = $this->scope->getSpan();

        $span->setTag(HTTP_STATUS_CODE, $response->getCode());

        $headers = [];

        $this->tracer->inject($span->getContext(), TEXT_MAP, $headers);

        return $response->header($headers);
    }

    public function end()
    {
        if ($this->scope) {
            $this->scope->close();
        }
        $this->tracer->flush();
    }

}
