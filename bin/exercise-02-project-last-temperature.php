#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

use BadMethodCallException;
use EventSourcingWorkshop\Glue\Application\Kernel;

(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

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
    throw new BadMethodCallException('Incomplete: remove me once finished with the exercise!');
})();
