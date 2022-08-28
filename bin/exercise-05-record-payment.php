#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\Payment\Domain\Aggregate\Payment;
use EventSourcingWorkshop\Payment\Domain\Aggregate\PaymentId;
use EventSourcingWorkshop\Payment\Domain\Amount;
use Psl\Env;
use Psl\Type;
use Throwable;
use UnexpectedValueException;

/**
 * Usage: ./exercise-05-record-payment.php <non-empty-string $paymentId> <positive-int $amount>
 *
 * Given the ID of a payment, this script will perform the payment for the given amount.
 */
(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    try {
        [, $paymentId, $amount] = Type\shape([
            1 => Type\non_empty_string(),
            2 => Type\positive_int(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException(
            'Usage: ./exercise-05-record-payment.php  <non-empty-string $paymentId> <positive-int $amount>',
            previous: $e,
        );
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    $repository = $kernel->aggregateRepository(Payment::class);

    $repository->save(
        $repository
            ->get(new PaymentId($paymentId))
            ->pay(new Amount($amount), $kernel->clock->now()),
    );
})();
