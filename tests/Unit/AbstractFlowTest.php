<?php

use Andaniel05\Flows\AbstractFlow;
use Andaniel05\Flows\Attributes\Step;

test('case 1', function () {
    $record = new stdClass;
    $record->items = [];

    $myFlow = new class($record) extends AbstractFlow {

        public function __construct(
            public stdClass $record
        ) {}

        #[Step]
        public function step1(): void
        {
            $this->record->items[] = 'one';
        }

        #[Step]
        public function step2(): void
        {
            $this->record->items[] = 'two';
        }

        #[Step]
        public function step3(): void
        {
            $this->record->items[] = 'three';
        }

        #[Step(branchName: 'branch1')]
        public function step4(): void
        {
            $this->record->items[] = 'four';
        }
    };

    $myFlow->run();

    expect($record->items)->toBe(['one', 'two', 'three']);
});

test('case 2', function () {
    $record = new stdClass;
    $record->items = [];

    $myFlow = new class($record) extends AbstractFlow {

        public function __construct(
            public stdClass $record
        ) {}

        #[Step]
        public function step1(): void
        {
            $this->record->items[] = 'one';
        }

        #[Step]
        public function step2(): void
        {
            $this->record->items[] = 'two';
        }

        #[Step]
        public function step3(): void
        {
            $this->record->items[] = 'three';
            $this->continueToBranch('branch1');
        }

        #[Step(branchName: 'branch1')]
        public function step4(): void
        {
            $this->record->items[] = 'four';

            if (! $this->isAborted()) {
                $this->abort();
            }
        }

        #[Step(branchName: 'branch1')]
        public function step5(): void
        {
            $this->record->items[] = 'five';
        }
    };

    $myFlow->run();

    expect($record->items)->toBe(['one', 'two', 'three', 'four']);
});