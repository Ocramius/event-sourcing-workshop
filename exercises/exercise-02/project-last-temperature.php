#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\TemperatureTracking\Domain\TemperatureRecorded;

use function file_exists;
use function file_put_contents;
use function json_encode;
use function Psl\File\read;
use function Psl\Json\typed;
use function Psl\Type\dict;
use function Psl\Type\float;
use function Psl\Type\non_empty_string;

(static function (): void {
    require_once __DIR__ . '/../../vendor/autoload.php';

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    /**
     * Here we want to:
     *
     * 1. iterate over recorded temperatures (tip: check the `$kernel`'s {@see Kernel::$traverseEventStream})
     * 2. generate an `array<string, float>` containing the last known temperature at each location
     * 3. save all accumulated temperatures to a file
     *
     * Question: what happens when you run this script multiple times?
     * Question: can you record new temperatures, and track them?
     */

    /** @var array<non-empty-string, float> $temperatures */
    $temperatures = [];

    if (file_exists(__DIR__ . '/../../data/last-temperatures.json')) {
        $temperatures = typed(
            read(__DIR__ . '/../../data/last-temperatures.json'),
            dict(
                non_empty_string(),
                float(),
            ),
        );
    }

    foreach (($kernel->traverseEventStream)('last-recorded-temperatures') as $event) {
        if (! $event instanceof TemperatureRecorded) {
            continue;
        }

        $temperatures[$event->location] = $event->celsius;
    }

    file_put_contents(__DIR__ . '/../../data/last-temperatures.json', json_encode($temperatures));
})();
