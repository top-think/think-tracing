# ThinkPHP链路追踪组件

## 安装
> composer require topthink/think-tracing

### 支持两种驱动zipkin、jaeger 分别需要安装
> composer require jcchavezs/zipkin-opentracing  
> composer require jonahgeorge/jaeger-client-php

## 使用
### 1. 中间件

扩展内提供了`think\tracing\middleware\TraceRequests`中间，配置到路由里或者全局中间件

### 2. 错误处理
> 使用了中间件后，该组件默认会跟踪到错误  
> 配置文件里errors设置为false可以关闭
> 

### 3. 其他使用方法参考 [opentracing](https://github.com/opentracing/opentracing-php)
