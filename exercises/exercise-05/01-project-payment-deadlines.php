#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use BadMethodCallException;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProcessProjectionOnTable;
use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;

/**
 * Usage: ./01-project-payment-deadlines.php
 *
 * Runs DB projections related to {@see Payment} logic.
 */
(static function (): void {
    require_once __DIR__ . '/../../vendor/autoload.php';

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan([
        CreateEventStreamTable::class,
        CreateEventStreamCursorsTable::class,
        // @TODO write this DB migration:
        // \EventSourcingWorkshop\Payment\Infrastructure\Migration\CreatePendingPaymentsProjectionTable::class,
    ]);

    /**
     * @TODO 1. write the {@see \EventSourcingWorkshop\Payment\Infrastructure\Migration\CreatePendingPaymentsProjectionTable}
     *          projection
     * @TODO 2. design the {@see \EventSourcingWorkshop\Payment\Infrastructure\Projection\TrackPaymentDeadlines} projection
     *          definition by implementing {@see \EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition}
     * @TODO 3. use {@see ProcessProjectionOnTable} to run the projection here
     */
    throw new BadMethodCallException('Complete the implementation below and remove this exception');

    //ProcessProjectionOnTable::forDefinition(
    //    // @TODO write this projection definition
    //    // note: implement \EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\DbTableProjectionDefinition
    //    new \EventSourcingWorkshop\Payment\Infrastructure\Projection\TrackPaymentDeadlines(),
    //    $kernel->db,
    //    $kernel->traverseEventStream,
    //)();
})();
