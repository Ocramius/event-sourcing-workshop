#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\LatePaymentTracking;

use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\TimeTracking\Domain\Date;
use EventSourcingWorkshop\TimeTracking\Domain\DomainEvent\ADayHasPassed;
use Psl\Env;
use Psl\Type;
use Throwable;
use UnexpectedValueException;

/**
 * Usage: ./exercise-05-record-day-passed.php <string $location> <float $celsius>
 *
 * This script injects an {@see ADayHasPassed} event in the event store: this would be used in
 * a nightly cronjob or such, to record the fact that a day has passed, allowing policies to
 * pick it up and start working from there.
 */
(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    try {
        [, $date] = Type\shape([
            1 => Type\non_empty_string(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException(
            'Usage: ./exercise-05-record-day-passed.php <string (Y-m-d) $date>',
            previous: $e,
        );
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    $kernel->eventStore->save(new ADayHasPassed(
        new Date($date),
        $kernel->clock->now(),
    ));
})();
