<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Domain;

use DateTimeImmutable;

/** @psalm-immutable */
interface DomainEvent
{
    public function raisedAt(): DateTimeImmutable;

    /** @return array<non-empty-string, string|int|bool|float|array|null> */
    public function toArray(): array;
}
