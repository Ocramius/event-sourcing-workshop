#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EventSourcingWorkshop\Commanding\Infrastructure\HandleCommandThroughGivenCommandHandlers;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies;
use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Policy\WhenADayHasPassedThenSendNotificationsForLatePayments;
use EventSourcingWorkshop\Payment\Infrastructure\CommandHandler\HandleSendNotificationsForLatePayments;
use EventSourcingWorkshop\Payment\Infrastructure\Migration\CreatePendingPaymentsProjectionTable;

/**
 * Usage: ./exercise-05-run-payment-process.php
 *
 * Runs follow-up processes related to {@see Payment} logic, reacting to events
 * that are pertinent to payments.
 */
(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan([
        CreateEventStreamTable::class,
        CreateEventStreamCursorsTable::class,
        CreatePendingPaymentsProjectionTable::class,
    ]);

    /** @psalm-suppress InvalidArgument policies are event-type-specific, and therefore variance is a bit wonky here */
    (new ProcessPolicies(
        [new WhenADayHasPassedThenSendNotificationsForLatePayments()],
        new HandleCommandThroughGivenCommandHandlers([
            new HandleSendNotificationsForLatePayments($kernel->db),
        ]),
        $kernel->traverseEventStream,
    ))();
})();
