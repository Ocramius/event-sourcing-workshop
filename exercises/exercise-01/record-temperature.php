#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\Exercise\TemperatureTracking;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use BadMethodCallException;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\Glue\Application\Kernel;
use EventSourcingWorkshop\TemperatureTracking\Domain\TemperatureRecorded;
use Psl\Env;
use Psl\Type;
use Throwable;
use UnexpectedValueException;

/**
 * Usage: ./record-temperature.php <string $location> <float $celsius>
 */
(static function (): void {
    require_once __DIR__ . '/../../vendor/autoload.php';

    try {
        [, $location, $temperature] = Type\shape([
            1 => Type\non_empty_string(),
            2 => Type\float(),
        ])->coerce(Env\args());
    } catch (Throwable $e) {
        throw new UnexpectedValueException('Usage: ./record-temperature.php <string $location> <float $celsius>', previous: $e);
    }

    $kernel = new Kernel();

    $kernel->ensureMigrationsRan();

    /**
     * Here we want to:
     *
     * 1. create a new `TemperatureRecorded` {@see DomainEvent} implementation under ../src/TemperatureTracking/Domain
     * 2. raise the event
     * 2. save that event to the event store (tip: check the `$kernel`'s {@see Kernel::$eventStore})
     * 3. observe the event store
     *
     * Question: what was saved in the DB?
     */
    $kernel->eventStore->save(
        new TemperatureRecorded($location, $temperature, $kernel->clock->now()),
    );
})();
