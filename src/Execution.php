<?php

namespace Andaniel05\Flows;

class Execution
{
    protected string $currentBranchName = 'default';

    public ?\DateTimeImmutable $startedAt = null;

    public ?\DateTimeImmutable $abortedAt = null;

    public ?\DateTimeImmutable $terminatedAt = null;

    public ?\DateTimeImmutable $completedAt = null;

    public array $records = [];

    protected ?string $lastBranchBeforeContinue = null;

    public function __construct(
        readonly public Flow $flow,
    ) {}

    public function abort(): void
    {
        $this->abortedAt = new \DateTimeImmutable;
    }

    public function isAborted(): bool
    {
        return (bool) $this->abortedAt;
    }

    public function continueToBranch(string $branchName): void
    {
        $steps = $this->flow->getSteps();

        if (! isset($steps[$branchName])) {
            throw new \Exception("Missing branch with name '$branchName'.");
        }

        $this->lastBranchBeforeContinue = $this->currentBranchName;
        $this->currentBranchName = $branchName;
    }

    public function addRecord(Record $record): void
    {
        $this->records[] = $record;
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function getNextStep(): ?Step
    {
        if (! $this->startedAt) {
            return null;
        }

        $steps = $this->flow->getSteps();

        if (empty($steps)) {
            return null;
        }

        if (! isset($steps[$this->currentBranchName])) {
            return null;
        }

        /** @var Record */
        $lastRecord = array_last($this->records);

        if (! $lastRecord || $this->lastBranchBeforeContinue) {
            $this->lastBranchBeforeContinue = null;
            return array_first($steps[$this->currentBranchName]);
        }

        $lastStepName = $lastRecord->step->name;
        $branchSteps = $steps[$this->currentBranchName];

        if (isset($branchSteps[$lastStepName])) {
            $branchStepNames = array_keys($branchSteps);
            $lastStepIndex = array_search($lastStepName, $branchStepNames);

            if (! is_numeric($lastStepIndex)) {
                return null;
            }

            if ($lastStepIndex === count($branchStepNames) - 1) {
                return null;
            }

            $nextStepIndex = $lastStepIndex + 1;
            $nextStepName = $branchStepNames[$nextStepIndex];

            return $branchSteps[$nextStepName];
        }

        return array_first($branchSteps);
    }
}