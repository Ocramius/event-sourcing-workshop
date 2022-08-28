#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use BadMethodCallException;
use EventSourcingWorkshop\Commanding\Infrastructure\HandleCommandThroughGivenCommandHandlers;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamCursorsTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Migration\CreateEventStreamTable;
use EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies;
use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Policy\WhenADayHasPassedThenSendNotificationsForLatePayments;
use EventSourcingWorkshop\Payment\Infrastructure\CommandHandler\HandleSendNotificationsForLatePayments;

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
        // @TODO write this DB migration. Complete `exercise-05-project-payment-deadlines.php` first!
        // \EventSourcingWorkshop\Payment\Infrastructure\Migration\CreatePendingPaymentsProjectionTable::class,
    ]);

    /**
     * @TODO 1. write a \EventSourcingWorkshop\Payment\Domain\Policy\WhenADayHasPassedThenSendNotificationsForLatePayments
     *          policy (implements \EventSourcingWorkshop\EventSourcing\Domain\Policy). Commands already exist.
     * @TODO 2. use \EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies to run it (see
     *          code snippet below)
     * @TODO 3. run `exercise-05-record-day-passed.php` with a future date as input
     *
     * Question: what happens when you run this script?
     * Question: what command handler is getting executed?
     * Question: what happens if you run this script again?
     */
    throw new BadMethodCallException('Complete the implementation below and remove this exception');

    ///** @psalm-suppress InvalidArgument policies are event-type-specific, and therefore variance is a bit wonky here */
    //(new ProcessPolicies(
    //    [new WhenADayHasPassedThenSendNotificationsForLatePayments()],
    //    new HandleCommandThroughGivenCommandHandlers([
    //        new HandleSendNotificationsForLatePayments($kernel->db),
    //    ]),
    //    $kernel->traverseEventStream,
    //))();
})();
