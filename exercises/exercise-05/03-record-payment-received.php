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
 * Usage: ./03-record-payment-received.php <non-empty-string $paymentId> <positive-int $amount>
 *
 * Given the ID of a payment, this script will perform the payment for the given amount.
 */
(static function (): void {
    require_once __DIR__ . '/../../vendor/autoload.php';

    try {
        [, $paymentId, $amount] = Type\shape([
            1 => Type\non_empty_string(),
            2 => Type\positive_int(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException(
            'Usage: ./03-record-payment-received.php  <non-empty-string $paymentId> <positive-int $amount>',
            previous: $e,
        );
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    /**
     * 1. fetch the existing {@see Payment} from the {@see AggregateRepository}. Tip: use the $kernel!
     * 2. mark the {@see Payment} as paid
     * 3. save the result!
     * 4. verify the data in the DB
     */
    $repository = $kernel->aggregateRepository(Payment::class);

    $payment = $repository->get(new PaymentId($paymentId));
    $result  = $payment->pay(new Amount($amount), $kernel->clock->now());

    $repository->save($result);
})();
