#!/usr/bin/env php
<?php
/**
 * Usage: ./project-last-temperature.php
 */

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

use BadMethodCallException;
use EventSourcingWorkshop\Glue\Application\Kernel;

use function var_dump;

(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    $kernel = new Kernel();
    
    $kernel->ensureMigrationsRan();

    /**
     * Here we want to:
     *
     * 1. iterate over recorded temperatures (Tip: look at `$kernel` properties!)
     * 2. accumulate the latest temperature for each location
     * 3. save all accumulated temperatures to a file
     *
     * Question: what happens when you run this script multiple times?
     * Question: can you record new temperatures, and track them?
     */
    throw new BadMethodCallException('Incomplete: remove me once finished with the exercise!');
})();
