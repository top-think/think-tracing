<?php

namespace think\tracing;

use think\event\LogRecord;
use const OpenTracing\Tags\ERROR;

class Service extends \think\Service
{
    public function boot(): void
    {
        if ($this->app->config->get('tracing.errors', false)) {
            $this->app->event->listen(LogRecord::class, function (Tracer $tracer, LogRecord $event) {
                if ($event->type == 'error') {
                    $span = $tracer->getActiveSpan();
                    if ($span) {
                        $span->setTag(ERROR, $event->message);
                    }
                }
            });
        }
    }
}
