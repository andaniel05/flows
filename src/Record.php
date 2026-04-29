<?php

namespace Andaniel05\Flows;

readonly class Record
{
    public function __construct(
        public Execution $execution,
        public Step $step,
        public \DateTimeImmutable $createdAt,
    ) {}
}