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
 * Usage: ./02-request-payment <non-empty-string $emailAddress> <positive-int $amount>
 *
 * This script initiates the request for a new payment
 *
 * @psalm-suppress UnusedVariable these variables will be in use once the exercise is complete
 */
(static function (): void {
    require_once __DIR__ . '/../../vendor/autoload.php';

    try {
        [, $emailAddress, $amount] = Type\shape([
            1 => Type\non_empty_string(),
            2 => Type\positive_int(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException(
            'Usage: ./02-request-payment <non-empty-string $emailAddress> <positive-int $amount>',
            previous: $e,
        );
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    /**
     * @TODO 1. create a new {@see Payment} (look at its public API) using the given $emailAddress and $amount
     * @TODO 2. save it to the DB!
     * @TODO 3. verify the data in the DB
     *
     * Question: what got saved?
     * Question: what is different between an {@see AggregateDomainEvent} and a {@see DomainEvent}?
     */
    throw new BadMethodCallException('Complete the implementation below and remove this exception');
})();
