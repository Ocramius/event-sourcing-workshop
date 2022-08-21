#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

use BadMethodCallException;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\Glue\Application\Kernel;
use Psl\Env;
use Psl\Type;
use Throwable;
use UnexpectedValueException;

/**
 * Usage: ./exercise-01-record-temperature <string $location> <float $celsius>
 *
 * @psalm-suppress UnusedVariable until the exercise is complete, some unused symbols may be sitting around
 */
(static function (): void {
    require_once __DIR__ . '/../vendor/autoload.php';

    try {
        [, $location, $temperature] = Type\shape([
            1 => Type\string(),
            2 => Type\float(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException('Usage: ./exercise-01-record-temperature <string $location> <float $celsius>', previous: $e);
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    /**
     * Here we want to:
     *
     * 1. create a new `TemperatureRecorded` {@see DomainEvent} implementation under ../src/TemperatureTracking/Domain
     * 2. raise the event
     * 2. save that event to the event store (tip: check $kernel properties)
     * 3. observe the event store
     *
     * Question: what was saved in the DB?
     */
    throw new BadMethodCallException('Incomplete: remove me once finished with the exercise!');
})();
