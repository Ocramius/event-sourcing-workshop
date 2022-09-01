<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TemperatureTracking\Domain;

use EventSourcingWorkshop\Commanding\Domain\Command;

/** @psalm-immutable */
final class SendTemperatureBelowZeroAlert implements Command
{
    /** @param non-empty-string $location */
    public function __construct(
        public readonly string $location,
        public readonly float $celsius,
    ) {
    }
}
