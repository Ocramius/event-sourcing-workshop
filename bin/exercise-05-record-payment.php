#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

use BadMethodCallException;
use EventSourcingWorkshop\Glue\Application\Kernel;
use Psl\Env;
use Psl\Type;
use Throwable;
use UnexpectedValueException;

/**
 * Usage: ./exercise-05-record-payment.php <non-empty-string $paymentId> <positive-int $amount>
 *
 * Given the ID of a payment, this script will perform the payment for the given amount.
 *
 * @psalm-suppress UnusedVariable these variables will be in use once the exercise is complete
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

    /**
     * @TODO 1. fetch the existing {@see Payment} from the {@see AggregateRepository}. Tip: use the $kernel!
     * @TODO 2. mark the {@see Payment} as paid
     * @TODO 3. save the result!
     * @TODO 4. verify the data in the DB
     */
    throw new BadMethodCallException('Complete the implementation below and remove this exception');
})();
