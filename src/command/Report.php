<?php

namespace think\tracing\command;

use think\console\Command;
use think\console\input\Option;
use think\tracing\Tracer;

class Report extends Command
{
    protected function configure()
    {
        $this->setName('tracing:report')
            ->addOption('tracer', '', Option::VALUE_OPTIONAL, 'witch trace', null);
    }

    public function handle(Tracer $tracer)
    {
        $name = $this->input->getOption('tracer') ?: $tracer->getDefaultDriver();

        $tracer = $tracer->tracer($name);

        $tracer->report();
    }

}
