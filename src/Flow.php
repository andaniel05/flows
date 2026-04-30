<?php

namespace Andaniel05\Flows;

use DateTimeImmutable;

class Flow
{
    protected array $steps = [];

    protected array $initExecutionListeners = [];

    public function addStep(string|callable $name, ?callable $handler = null, string $branchName = 'default'): void
    {
        $this->steps[$branchName] ??= [];

        if (is_callable($name)) {
            $handler = $name;
            $name = count($this->steps[$branchName]);
        }

        if (isset($this->steps[$branchName][$name])) {
            throw new \Exception("Duplicated step with name '$name' for branch '$branchName'.");
        }

        $step = new Step($this, $branchName, $name, $handler);

        $this->steps[$branchName][$name] = $step;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function addInitExecutionListener(callable $listener): void
    {
        $this->initExecutionListeners[] = $listener;
    }

    public function clearInitExecutionListeners(): void
    {
        $this->initExecutionListeners = [];
    }

    public function run(): Execution
    {
        $execution = new Execution($this);

        foreach ($this->initExecutionListeners as $initExecutionListener) {
            $initExecutionListener($execution);
        }

        $execution->startedAt = new \DateTimeImmutable;

        while ($step = $execution->getNextStep()) {
            $step->run($execution);

            $record = new Record(
                $execution,
                $step,
                createdAt: new DateTimeImmutable
            );

            $execution->addRecord($record);

            if ($execution->abortedAt) {
                break;
            }
        }

        $execution->terminatedAt = new DateTimeImmutable;

        if (! $execution->abortedAt) {
            $execution->completedAt = $execution->terminatedAt;
        }

        return $execution;
    }
}