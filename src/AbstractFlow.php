<?php

namespace Andaniel05\Flows;

use Andaniel05\Flows\Attributes\Step as StepAttribute;
use Andaniel05\Flows\Execution;

abstract class AbstractFlow
{
    protected ?Execution $currentExecution = null;

    public static function getBranches(): array
    {
        $branches = [];

        $class = new \ReflectionClass(static::class);
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
            $branchName = $stepAttribute->branchName;

            $branches[$branchName] ??= [];
            $branches[$branchName][] = $methodName;
        }

        return $branches;
    }

    public function abort(): void
    {
        $this->currentExecution?->abort();
    }

    public function isAborted(): ?bool
    {
        return $this->currentExecution?->isAborted();
    }

    public function continueToBranch(string $branchName): void
    {
        $this->currentExecution?->continueToBranch($branchName);
    }

    public function execute(): Execution
    {
        $flow = new Flow;

        $flow->addInitExecutionListener(function (Execution $execution) {
            $this->currentExecution = $execution;
        });

        $class = new \ReflectionClass($this);
        $publicMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        // build steps
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

        $this->currentExecution = null;

        return $flow->execute();
    }
}