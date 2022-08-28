#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\ProcessProjectionOnTable;
use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Infrastructure\Migration\CreatePendingPaymentsProjectionTable;
use EventSourcingWorkshop\Payment\Infrastructure\Projection\TrackPaymentDeadlines;

/**
 * Usage: ./exercise-05-project-payment-deadlines.php
 *
 * Runs DB projections related to {@see Payment} logic.
 */
(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan([
        CreateEventStreamTable::class,
        CreateEventStreamCursorsTable::class,
        CreatePendingPaymentsProjectionTable::class,
    ]);

    ProcessProjectionOnTable::forDefinition(
        new TrackPaymentDeadlines(),
        $kernel->db,
        $kernel->traverseEventStream,
    )();
})();
