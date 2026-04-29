<?php

namespace Andaniel05\Flows;

use Andaniel05\Flows\Attributes\Step as StepAttribute;
use Andaniel05\Flows\Execution;

abstract class AbstractFlow
{
    public function run(): Execution
    {
        $flow = new Flow;

        $class = new \ReflectionClass($this);
        $publicMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $method) {
            $stepAttributes = $method->getAttributes(StepAttribute::class);

            /** @var \ReflectionAttribute|null */
            $stepReflectionAttribute = array_last($stepAttributes);

            if (! $stepReflectionAttribute) {
                continue;
            }

            $methodName = $method->getName();
            $stepAttribute = $stepReflectionAttribute->newInstance();

            $flow->addStep(
                name: $methodName,
                handler: [$this, $methodName],
                branchName: $stepAttribute->branchName
            );
        }

        return $flow->run();
    }
}