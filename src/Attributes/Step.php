<?php

namespace Andaniel05\Flows\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class Step
{
    public function __construct(public string $branchName = 'default') {}
}