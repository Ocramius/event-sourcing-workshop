#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Amount;
use EventSourcingWorkshop\Payment\Domain\DebtorEmail;
use Psl\Env;
use Psl\Type;
use Throwable;
use UnexpectedValueException;

/**
 * Usage: ./exercise-05-request-payment <non-empty-string $emailAddress> <positive-int $amount>
 *
 * This script initiates the request for a new payment
 */
(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    try {
        [, $emailAddress, $amount] = Type\shape([
            1 => Type\non_empty_string(),
            2 => Type\positive_int(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException(
            'Usage: ./exercise-05-record-day-passed.php <string (Y-m-d) $date>',
            previous: $e,
        );
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    $kernel->aggregateRepository(Payment::class)
        ->save(Payment::requestPayment(
            new DebtorEmail($emailAddress),
            new Amount($amount),
            $kernel->clock->now(),
        ));
})();
