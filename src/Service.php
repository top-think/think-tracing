<?php

namespace think\tracing;

use think\event\LogRecord;
use think\tracing\command\Report;
use const OpenTracing\Tags\DATABASE_STATEMENT;
use const OpenTracing\Tags\ERROR;

class Service extends \think\Service
{
    public function boot(): void
    {
        $this->commands([
            Report::class,
        ]);

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

        if ($this->app->config->get('tracing.sql', false)) {
            $this->app->db->listen(function ($sql, $time) {
                if (0 !== strpos($sql, 'CONNECT:')) {
                    /** @var Tracer $tracer */
                    $tracer = app(Tracer::class);

                    $span = $tracer->startSpan('db_query', [
                        'start_time' => (int) ((microtime(true) - $time) * 1000 * 1000),
                    ]);

                    $span->setTag(DATABASE_STATEMENT, $sql);

                    $span->finish();
                }
            });
        }
    }
}
