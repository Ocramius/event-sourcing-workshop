<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TemperatureTracking\Domain;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/** @psalm-immutable */
final class TemperatureRecorded implements DomainEvent
{
    /** @param non-empty-string $location */
    public function __construct(
        public readonly string $location,
        public readonly float $celsius,
        public readonly DateTimeImmutable $raisedAt,
    ) {
    }

    public function raisedAt(): DateTimeImmutable
    {
        return $this->raisedAt;
    }

    /** {@inheritDoc} */
    public function toArray(): array
    {
        return [
            'location' => $this->location,
            'celsius'  => $this->celsius,
            'raisedAt' => $this->raisedAt->format(DateTimeImmutable::RFC3339_EXTENDED),
        ];
    }
}
