# ThinkPHP链路追踪组件

## 安装
> composer require topthink/think-tracing

### 支持两种驱动zipkin、jaeger 分别需要安装
> composer require jcchavezs/zipkin-opentracing
> 
> composer require jonahgeorge/jaeger-client-php

## 使用
### 1. 中间件

扩展内提供了`think\tracing\middleware\TraceRequests`中间，配置到路由里或者全局中间件

### 2. 异常处理
> 仅供参考
> 
> 修改ExceptionHandler

```php
use think\tracing\Tracer;
use const OpenTracing\Tags\ERROR;

...

public function __construct(App $app, Tracer $tracer)
{
    parent::__construct($app);
    $this->tracer = $tracer;
}

/**
 * 记录异常信息（包括日志或者其它方式记录）
 *
 * @access public
 * @param Throwable $ex
 * @return void
 */
public function report(Throwable $ex): void
{
    $span = $this->tracer->getActiveSpan();
    if ($span) {
        $span->setTag(ERROR, true);
        $span->log([
            'event'        => 'error',
            'error.object' => $ex,
        ]);
    }
    // 使用内置的方式记录异常日志
    parent::report($ex);
}

...

```

### 3. 其他使用方法参考 [opentracing](https://github.com/opentracing/opentracing-php)
