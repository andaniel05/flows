<?php

namespace Andaniel05\Flows;

readonly class Step
{
    public function __construct(
        public Flow $flow,
        public string $branchName,
        public string $name,
        public mixed $handler,
    ) {
        if (! is_callable($handler)) {
            throw new \Exception();
        }
    }

    public function run(Execution $execution): void
    {
        call_user_func($this->handler, $execution);
    }
}