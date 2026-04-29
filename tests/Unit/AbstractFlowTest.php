<?php

use Andaniel05\Flows\AbstractFlow;
use Andaniel05\Flows\Attributes\Step;

test('', function () {
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
    };

    $myFlow->run();

    expect($record->items)->toBe(['one', 'two', 'three']);
});