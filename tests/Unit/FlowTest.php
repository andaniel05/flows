<?php

use Andaniel05\Flows\Execution;
use Andaniel05\Flows\Flow;

beforeEach(function () {
    $this->flow = new Flow;
    $this->result = [];
});

describe('add the first anonymous step handler', function () {
    beforeEach(function () {
        $this->flow->addStep(function () {
            $this->result[] = 'one';
        });
    });

    describe('add the second anonymous step handler', function () {
        beforeEach(function () {
            $this->flow->addStep(function () {
                $this->result[] = 'two';
            });
        });

        test('when flow is executed without subject both handlers are also executed', function () {
            $this->flow->execute();

            expect($this->result)->toBe(['one', 'two']);
        });
    });

    describe('add two handlers. The first of them abort the execution', function () {
        beforeEach(function () {
            $this->flow->addStep(function (Execution $execution) {
                $this->result[] = 'two';

                $execution->abort();
            });

            $this->flow->addStep(function () {
                $this->result[] = 'three';
            });
        });

        test('when the flow is executed only the two top handlers were executed', function () {
            $this->flow->execute();

            expect($this->result)->toBe(['one', 'two']);
        });
    });
});

describe('', function () {
    test('test1', function () {
        $this->flow->addStep(function () {
            $this->result[] = 'one';
        });

        $this->flow->addStep(function () {
            $this->result[] = 'two';
        });

        $this->flow->addStep(function () {
            $this->result[] = 'three';
        });

        $this->flow->addStep(function () {
            $this->result[] = 'four';
        }, branchName: 'branch1');

        $this->flow->addStep(function () {
            $this->result[] = 'five';
        }, branchName: 'branch1');

        $this->flow->execute();

        expect($this->result)->toBe(['one', 'two', 'three']);
    });

    test('test2', function () {
        $this->flow->addStep(function () {
            $this->result[] = 'one';
        });

        $this->flow->addStep(function () {
            $this->result[] = 'two';
        });

        $this->flow->addStep(function (Execution $execution) {
            $this->result[] = 'three';

            $execution->continueToBranch('branch1');
        });

        $this->flow->addStep(function () {
            $this->result[] = 'four';
        }, branchName: 'branch1');

        $this->flow->addStep(function (Execution $execution) {
            $this->result[] = 'five';

            $execution->abort();
        }, branchName: 'branch1');

        $this->flow->addStep(function () {
            $this->result[] = 'six';
        }, branchName: 'branch1');

        $this->flow->execute();

        expect($this->result)->toBe(['one', 'two', 'three', 'four', 'five']);
    });
});