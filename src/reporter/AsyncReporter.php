<?php

namespace think\tracing\reporter;

interface AsyncReporter
{
    public function flush();
}
