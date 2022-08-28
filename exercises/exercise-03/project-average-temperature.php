#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use BadMethodCallException;
use Doctrine\Migrations\AbstractMigration;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProcessProjectionOnTable;
use EventSourcingWorkshop\Glue\Application\Kernel;

(static function (): void {
    require_once __DIR__ . '/../../vendor/autoload.php';

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan(/** @TODO add your own DB migration here! */);

    /**
     * Here we want to:
     *
     * @TODO 1. write an {@see AbstractMigration} that creates the DB table that will host your projection data, pass
     *          it to {@see Kernel::ensureMigrationsRan()}.
     * @TODO 2. write a {@see DbTableProjectionDefinition} implementation under ../src/TemperatureTracking/Infrastructure
     * @TODO 3. create a {@see ProcessProjectionOnTable} instance
     * @TODO 4. run it
     */
    throw new BadMethodCallException('Incomplete: remove me once finished with the exercise!');
})();
