#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use BadMethodCallException;
use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;
use EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies;
use EventSourcingWorkshop\Glue\Application\Kernel;

(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    /**
     * Here we want to:
     *
     * @TODO 1. create a new `WhenTemperatureBelowZeroSendAlert` {@see Policy} implementation
     * @TODO 2. create a new `SendTemperateBelowZeroAlert` {@see Command} implementation
     * @TODO 3. create a new `HandleSendTemperateBelowZeroAlert` {@see CommandHandler} implementation
     *          It should only print some alert message to `STDERR` via {@see \error_log()}, for now.
     * @TODO 4. wire it together with {@see ProcessPolicies} src/EventSourcing/Infrastructure/ProcessManager/ProcessPolicies.php)
     * @TODO 5. run the created {@see ProcessPolicies}, see if you can get the alerts fired
     *
     * Question: what happens when you run the script multiple times?
     * Question: what happens when new events appear, and you run the script again?
     * Question: how should we deal with failures/crashes here?
     */
    throw new BadMethodCallException('Incomplete: remove me once finished with the exercise!');
})();
