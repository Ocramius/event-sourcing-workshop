<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\TimeTracking\Domain\DomainEvent;

use DateTimeImmutable;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;
use EventSourcingWorkshop\TimeTracking\Domain\Date;

/** @psalm-immutable */
final class ADayHasPassed implements DomainEvent
{
    public function __construct(
        public readonly Date $newDayDate,
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
            'newDayDate' => $this->newDayDate->date->format('Y-m-d'),
            'raisedAt'   => $this->raisedAt->format(DateTimeImmutable::RFC3339_EXTENDED),
        ];
    }
}
